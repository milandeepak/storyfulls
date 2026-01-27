#!/usr/bin/env python3
"""
Migrate books from old Storyfulls site to new Drupal 11 site
"""

import requests
import json
import os
import re
import time
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse

# Configuration
OLD_SITE_URL = "https://storyfulls.com"
LOGIN_URL = f"{OLD_SITE_URL}/user/login"
CONTENT_URL = f"{OLD_SITE_URL}/admin/content"
USERNAME = "storyfullsadmin"
PASSWORD = "Storyfulls@!23"

# Output directory for images
IMAGE_DIR = "/home/milan/projects/storyfulls-d11/web/themes/custom/storyfulls/images/books"
OUTPUT_JSON = "/home/milan/projects/storyfulls-d11/scripts/migrated_books.json"

# Create image directory if it doesn't exist
os.makedirs(IMAGE_DIR, exist_ok=True)

class StoryfullsMigrator:
    def __init__(self):
        self.session = requests.Session()
        self.books = []
        
    def login(self):
        """Login to the old Storyfulls site"""
        print("Logging in to old site...")
        
        # First get the login page to get form_build_id
        login_page = self.session.get(LOGIN_URL)
        soup = BeautifulSoup(login_page.content, 'html.parser')
        
        form_build_id = soup.find('input', {'name': 'form_build_id'})
        form_token = soup.find('input', {'name': 'form_token'})
        
        login_data = {
            'name': USERNAME,
            'pass': PASSWORD,
            'form_id': 'user_login_form',
        }
        
        if form_build_id:
            login_data['form_build_id'] = form_build_id['value']
        if form_token:
            login_data['form_token'] = form_token['value']
        
        response = self.session.post(LOGIN_URL, data=login_data, allow_redirects=True)
        
        if response.status_code == 200:
            print("✓ Login successful")
            return True
        else:
            print(f"✗ Login failed: {response.status_code}")
            return False
    
    def get_all_book_ids(self):
        """Get all book IDs from the content page"""
        print("\nFetching all book IDs...")
        book_ids = set()
        page = 0
        
        while True:
            url = f"{CONTENT_URL}?type=book&page={page}"
            print(f"  Fetching page {page}...")
            response = self.session.get(url)
            
            if response.status_code != 200:
                break
            
            soup = BeautifulSoup(response.content, 'html.parser')
            
            # Find all book links
            links = soup.find_all('a', href=re.compile(r'/book/\d+'))
            
            if not links:
                break
            
            for link in links:
                match = re.search(r'/book/(\d+)', link['href'])
                if match:
                    book_ids.add(match.group(1))
            
            # Check if there's a next page
            next_link = soup.find('a', {'rel': 'next'})
            if not next_link:
                break
            
            page += 1
            time.sleep(0.5)  # Be nice to the server
        
        print(f"✓ Found {len(book_ids)} books")
        return sorted(book_ids)
    
    def download_image(self, image_url, book_id):
        """Download an image and save it locally"""
        if not image_url:
            return None
        
        # Make URL absolute
        if not image_url.startswith('http'):
            image_url = urljoin(OLD_SITE_URL, image_url)
        
        try:
            # Get file extension
            ext = os.path.splitext(urlparse(image_url).path)[1]
            if not ext or ext.lower() not in ['.jpg', '.jpeg', '.png', '.gif', '.webp']:
                ext = '.jpg'
            
            # Create filename
            filename = f"book_{book_id}{ext}"
            filepath = os.path.join(IMAGE_DIR, filename)
            
            # Download image
            response = self.session.get(image_url, stream=True)
            if response.status_code == 200:
                with open(filepath, 'wb') as f:
                    for chunk in response.iter_content(1024):
                        f.write(chunk)
                return filename
            
        except Exception as e:
            print(f"    ⚠ Failed to download image: {e}")
        
        return None
    
    def scrape_book(self, book_id):
        """Scrape all data for a single book"""
        url = f"{OLD_SITE_URL}/book/{book_id}"
        print(f"\n  Scraping book {book_id}...")
        
        response = self.session.get(url)
        if response.status_code != 200:
            print(f"    ✗ Failed to fetch book page")
            return None
        
        soup = BeautifulSoup(response.content, 'html.parser')
        
        book_data = {
            'old_id': book_id,
            'title': '',
            'description': '',
            'cover_image': None,
            'author': [],
            'illustrator': [],
            'publisher': '',
            'age_group': '',
            'genres': [],
            'isbn': '',
            'pages': '',
            'language': '',
            'publication_year': '',
        }
        
        # Extract title
        title_elem = soup.find('h1') or soup.find('title')
        if title_elem:
            book_data['title'] = title_elem.get_text().strip().replace(' | Storyfulls', '')
        
        # Extract description
        desc_elem = soup.find('div', class_=re.compile(r'field--name-body|field--type-text-with-summary'))
        if desc_elem:
            book_data['description'] = desc_elem.get_text().strip()
        
        # Extract cover image
        img_elem = soup.find('img', class_=re.compile(r'field--name-field-featured-image|field--type-image'))
        if not img_elem:
            img_elem = soup.find('div', class_='field--name-field-featured-image').find('img') if soup.find('div', class_='field--name-field-featured-image') else None
        
        if img_elem and img_elem.get('src'):
            image_filename = self.download_image(img_elem['src'], book_id)
            book_data['cover_image'] = image_filename
            print(f"    ✓ Downloaded image: {image_filename}")
        
        # Extract author(s)
        author_elem = soup.find('div', class_=re.compile(r'field--name-field-author'))
        if author_elem:
            authors = author_elem.find_all('a')
            if authors:
                book_data['author'] = [a.get_text().strip() for a in authors]
            else:
                book_data['author'] = [author_elem.get_text().strip()]
        
        # Extract illustrator(s)
        illustrator_elem = soup.find('div', class_=re.compile(r'field--name-field-illustrator'))
        if illustrator_elem:
            illustrators = illustrator_elem.find_all('a')
            if illustrators:
                book_data['illustrator'] = [i.get_text().strip() for i in illustrators]
            else:
                book_data['illustrator'] = [illustrator_elem.get_text().strip()]
        
        # Extract publisher
        publisher_elem = soup.find('div', class_=re.compile(r'field--name-field-publisher'))
        if publisher_elem:
            book_data['publisher'] = publisher_elem.get_text().strip()
        
        # Extract age group
        age_elem = soup.find('div', class_=re.compile(r'field--name-field-age'))
        if age_elem:
            book_data['age_group'] = age_elem.get_text().strip()
        
        # Extract genres/tags
        genre_elem = soup.find('div', class_=re.compile(r'field--name-field-genre|field--name-field-tags'))
        if genre_elem:
            genres = genre_elem.find_all('a')
            if genres:
                book_data['genres'] = [g.get_text().strip() for g in genres]
        
        # Extract ISBN
        isbn_elem = soup.find('div', class_=re.compile(r'field--name-field-isbn'))
        if isbn_elem:
            book_data['isbn'] = isbn_elem.get_text().strip()
        
        # Extract pages
        pages_elem = soup.find('div', class_=re.compile(r'field--name-field-pages'))
        if pages_elem:
            book_data['pages'] = pages_elem.get_text().strip()
        
        # Extract language
        lang_elem = soup.find('div', class_=re.compile(r'field--name-field-language'))
        if lang_elem:
            book_data['language'] = lang_elem.get_text().strip()
        
        # Extract publication year
        year_elem = soup.find('div', class_=re.compile(r'field--name-field-publication-year|field--name-field-year'))
        if year_elem:
            book_data['publication_year'] = year_elem.get_text().strip()
        
        print(f"    ✓ Title: {book_data['title']}")
        print(f"    ✓ Author: {', '.join(book_data['author']) if book_data['author'] else 'N/A'}")
        print(f"    ✓ Age: {book_data['age_group']}")
        
        return book_data
    
    def migrate_all_books(self):
        """Main migration process"""
        print("\n" + "="*60)
        print("STORYFULLS BOOK MIGRATION")
        print("="*60)
        
        # Login
        if not self.login():
            return False
        
        # Get all book IDs
        book_ids = self.get_all_book_ids()
        
        if not book_ids:
            print("No books found!")
            return False
        
        # Scrape each book
        print(f"\nScraping {len(book_ids)} books...")
        for i, book_id in enumerate(book_ids, 1):
            print(f"\n[{i}/{len(book_ids)}] Processing book {book_id}...")
            book_data = self.scrape_book(book_id)
            
            if book_data:
                self.books.append(book_data)
            
            # Be nice to the server
            time.sleep(1)
        
        # Save to JSON
        print(f"\nSaving data to {OUTPUT_JSON}...")
        with open(OUTPUT_JSON, 'w') as f:
            json.dump(self.books, f, indent=2)
        
        print("\n" + "="*60)
        print(f"✓ Migration complete!")
        print(f"  Total books scraped: {len(self.books)}")
        print(f"  Data saved to: {OUTPUT_JSON}")
        print(f"  Images saved to: {IMAGE_DIR}")
        print("="*60)
        
        return True

if __name__ == "__main__":
    migrator = StoryfullsMigrator()
    migrator.migrate_all_books()
