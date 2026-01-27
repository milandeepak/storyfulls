<?php

/**
 * Migrate books from old Storyfulls.com to new Drupal 11 site
 * 
 * This script will:
 * 1. Login to old site
 * 2. Fetch all book data
 * 3. Download images
 * 4. Save to JSON for import
 */

// Configuration
define('OLD_SITE_URL', 'https://storyfulls.com');
define('LOGIN_URL', OLD_SITE_URL . '/user/login');
define('USERNAME', 'storyfullsadmin');
define('PASSWORD', 'Storyfulls@!23');
define('IMAGE_DIR', '/var/www/html/web/themes/custom/storyfulls/images/books');
define('OUTPUT_JSON', '/var/www/html/scripts/migrated_books.json');

// Create image directory
if (!is_dir(IMAGE_DIR)) {
  mkdir(IMAGE_DIR, 0755, true);
}

class BookMigrator {
  private $cookieFile;
  
  public function __construct() {
    $this->cookieFile = tempnam(sys_get_temp_dir(), 'cookies_');
  }
  
  public function __destruct() {
    if (file_exists($this->cookieFile)) {
      unlink($this->cookieFile);
    }
  }
  
  /**
   * Make a CURL request
   */
  private function curlRequest($url, $post = null, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36');
    
    if ($post !== null) {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    
    if (!empty($headers)) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['body' => $response, 'code' => $httpCode];
  }
  
  /**
   * Login to old site
   */
  public function login() {
    echo "Logging in to old site...\n";
    
    // Get login page first
    $response = $this->curlRequest(LOGIN_URL);
    
    // Extract form_build_id and form_token
    preg_match('/name="form_build_id" value="([^"]+)"/', $response['body'], $formBuildId);
    preg_match('/name="form_token" value="([^"]+)"/', $response['body'], $formToken);
    
    $postData = [
      'name' => USERNAME,
      'pass' => PASSWORD,
      'form_id' => 'user_login_form',
    ];
    
    if (!empty($formBuildId[1])) {
      $postData['form_build_id'] = $formBuildId[1];
    }
    if (!empty($formToken[1])) {
      $postData['form_token'] = $formToken[1];
    }
    
    $response = $this->curlRequest(LOGIN_URL, http_build_query($postData));
    
    if ($response['code'] == 200) {
      echo "✓ Login successful\n";
      return true;
    }
    
    echo "✗ Login failed\n";
    return false;
  }
  
  /**
   * Get all book IDs
   */
  public function getAllBookIds() {
    echo "\nFetching all book IDs...\n";
    $bookIds = [];
    $page = 0;
    
    while (true) {
      $url = OLD_SITE_URL . "/admin/content?type=book&page={$page}";
      echo "  Fetching page {$page}...\n";
      
      $response = $this->curlRequest($url);
      
      if ($response['code'] != 200) {
        break;
      }
      
      // Extract book IDs
      preg_match_all('/\/book\/(\d+)/', $response['body'], $matches);
      
      if (empty($matches[1])) {
        break;
      }
      
      $bookIds = array_merge($bookIds, $matches[1]);
      
      // Check for next page
      if (strpos($response['body'], 'rel="next"') === false) {
        break;
      }
      
      $page++;
      usleep(500000); // 0.5 second delay
    }
    
    $bookIds = array_unique($bookIds);
    sort($bookIds);
    
    echo "✓ Found " . count($bookIds) . " books\n";
    return $bookIds;
  }
  
  /**
   * Download an image
   */
  private function downloadImage($imageUrl, $bookId) {
    if (empty($imageUrl)) {
      return null;
    }
    
    // Make URL absolute
    if (strpos($imageUrl, 'http') !== 0) {
      $imageUrl = OLD_SITE_URL . $imageUrl;
    }
    
    // Get file extension
    $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (empty($ext) || !in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
      $ext = 'jpg';
    }
    
    $filename = "book_{$bookId}.{$ext}";
    $filepath = IMAGE_DIR . '/' . $filename;
    
    // Download image
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && !empty($imageData)) {
      file_put_contents($filepath, $imageData);
      return $filename;
    }
    
    return null;
  }
  
  /**
   * Extract text from HTML element
   */
  private function extractText($html, $pattern) {
    if (preg_match($pattern, $html, $match)) {
      return trim(strip_tags($match[1]));
    }
    return '';
  }
  
  /**
   * Extract multiple values (for taxonomies)
   */
  private function extractMultiple($html, $pattern) {
    $values = [];
    if (preg_match_all($pattern, $html, $matches)) {
      foreach ($matches[1] as $match) {
        $values[] = trim(strip_tags($match));
      }
    }
    return $values;
  }
  
  /**
   * Extract genres from field section
   */
  private function extractGenres($html) {
    $genres = [];
    
    // Find the genere field section first
    if (preg_match('/<div[^>]*class="[^"]*field--name-field-genere[^"]*"[^>]*>(.*?)<\/div>\s*<\/div>/s', $html, $section)) {
      // Now extract all field__item spans from that section
      if (preg_match_all('/<span class="field__item">([^<]+)<\/span>/', $section[1], $matches)) {
        foreach ($matches[1] as $genre) {
          // Clean up leading commas and whitespace
          $genre = trim($genre, ', ');
          if (!empty($genre)) {
            $genres[] = $genre;
          }
        }
      }
    }
    
    return $genres;
  }
  
  /**
   * Scrape a single book
   */
  public function scrapeBook($bookId) {
    $url = OLD_SITE_URL . "/book/{$bookId}";
    
    $response = $this->curlRequest($url);
    
    if ($response['code'] != 200) {
      echo "    ✗ Failed to fetch book page\n";
      return null;
    }
    
    $html = $response['body'];
    
    $bookData = [
      'old_id' => $bookId,
      'title' => '',
      'description' => '',
      'cover_image' => null,
      'author' => [],
      'illustrator' => [],
      'publisher' => '',
      'age_group' => '',
      'genres' => [],
      'isbn' => '',
      'pages' => '',
      'language' => '',
      'publication_year' => '',
    ];
    
    // Extract title
    $bookData['title'] = $this->extractText($html, '/<h1[^>]*>(.*?)<\/h1>/s');
    $bookData['title'] = str_replace(' | Storyfulls', '', $bookData['title']);
    
    // Extract description  
    if (preg_match('/<div[^>]*class="[^"]*field--name-field-description[^"]*"[^>]*>.*?<div class="field__item">(.*?)<\/div>/s', $html, $match)) {
      $bookData['description'] = trim(strip_tags($match[1], '<p><br>'));
    } elseif (preg_match('/<div[^>]*class="[^"]*field--name-body[^"]*"[^>]*>(.*?)<\/div>/s', $html, $match)) {
      $bookData['description'] = trim(strip_tags($match[1], '<p><br>'));
    }
    
    // Extract cover image
    if (preg_match('/<img[^>]*class="[^"]*field--name-field-featured-image[^"]*"[^>]*src="([^"]+)"/s', $html, $match) ||
        preg_match('/<div[^>]*class="[^"]*field--name-field-featured-image[^"]*"[^>]*>.*?<img[^>]*src="([^"]+)"/s', $html, $match)) {
      $imageFilename = $this->downloadImage($match[1], $bookId);
      if ($imageFilename) {
        $bookData['cover_image'] = $imageFilename;
        echo "    ✓ Downloaded image: {$imageFilename}\n";
      }
    }
    
    // Extract authors
    $bookData['author'] = $this->extractMultiple($html, '/<div[^>]*class="[^"]*field--name-field-author[^"]*"[^>]*>.*?<div class="field__item">(.*?)<\/div>/s');
    if (empty($bookData['author'])) {
      $bookData['author'] = $this->extractMultiple($html, '/<div[^>]*class="[^"]*field--name-field-author[^"]*"[^>]*>.*?<a[^>]*>(.*?)<\/a>/s');
    }
    
    // Extract illustrators
    $bookData['illustrator'] = $this->extractMultiple($html, '/<div[^>]*class="[^"]*field--name-field-illustrator[^"]*"[^>]*>.*?<div class="field__item">(.*?)<\/div>/s');
    if (empty($bookData['illustrator'])) {
      $bookData['illustrator'] = $this->extractMultiple($html, '/<div[^>]*class="[^"]*field--name-field-illustrator[^"]*"[^>]*>.*?<a[^>]*>(.*?)<\/a>/s');
    }
    
    // Extract publisher
    $bookData['publisher'] = $this->extractText($html, '/<div[^>]*class="[^"]*field--name-field-publisher[^"]*"[^>]*>.*?<div class="field__item">(.*?)<\/div>/s');
    
    // Extract age group
    $ageGroups = $this->extractMultiple($html, '/<div[^>]*class="[^"]*field--name-field-age-group[^"]*"[^>]*>.*?<span class="field__item">(.*?)<\/span>/s');
    $bookData['age_group'] = !empty($ageGroups) ? implode(', ', array_map('trim', $ageGroups)) : '';
    
    // Extract genres
    $bookData['genres'] = $this->extractGenres($html);
    // Fallback to other patterns if needed
    if (empty($bookData['genres'])) {
      $bookData['genres'] = $this->extractMultiple($html, '/<div[^>]*class="[^"]*field--name-field-tags[^"]*"[^>]*>.*?<div class="field__item">(.*?)<\/div>/s');
    }
    
    // Extract ISBN
    $bookData['isbn'] = $this->extractText($html, '/<div[^>]*class="[^"]*field--name-field-isbn[^"]*"[^>]*>(.*?)<\/div>/s');
    
    // Extract pages
    $bookData['pages'] = $this->extractText($html, '/<div[^>]*class="[^"]*field--name-field-pages[^"]*"[^>]*>(.*?)<\/div>/s');
    
    // Extract language
    $bookData['language'] = $this->extractText($html, '/<div[^>]*class="[^"]*field--name-field-language[^"]*"[^>]*>(.*?)<\/div>/s');
    
    // Extract publication year
    $bookData['publication_year'] = $this->extractText($html, '/<div[^>]*class="[^"]*field--name-field-(publication-)?year[^"]*"[^>]*>(.*?)<\/div>/s');
    
    echo "    ✓ Title: {$bookData['title']}\n";
    echo "    ✓ Author: " . (empty($bookData['author']) ? 'N/A' : implode(', ', $bookData['author'])) . "\n";
    echo "    ✓ Age: {$bookData['age_group']}\n";
    
    return $bookData;
  }
  
  /**
   * Main migration process
   */
  public function migrate() {
    echo str_repeat("=", 60) . "\n";
    echo "STORYFULLS BOOK MIGRATION\n";
    echo str_repeat("=", 60) . "\n";
    
    // Login
    if (!$this->login()) {
      return false;
    }
    
    // Get all book IDs
    $bookIds = $this->getAllBookIds();
    
    if (empty($bookIds)) {
      echo "No books found!\n";
      return false;
    }
    
    // Scrape each book
    $books = [];
    $total = count($bookIds);
    
    echo "\nScraping {$total} books...\n";
    
    foreach ($bookIds as $index => $bookId) {
      $num = $index + 1;
      echo "\n[{$num}/{$total}] Processing book {$bookId}...\n";
      
      $bookData = $this->scrapeBook($bookId);
      
      if ($bookData) {
        $books[] = $bookData;
      }
      
      // Be nice to the server
      sleep(1);
    }
    
    // Save to JSON
    echo "\nSaving data to " . OUTPUT_JSON . "...\n";
    file_put_contents(OUTPUT_JSON, json_encode($books, JSON_PRETTY_PRINT));
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✓ Migration complete!\n";
    echo "  Total books scraped: " . count($books) . "\n";
    echo "  Data saved to: " . OUTPUT_JSON . "\n";
    echo "  Images saved to: " . IMAGE_DIR . "\n";
    echo str_repeat("=", 60) . "\n";
    
    return true;
  }
}

// Run migration
$migrator = new BookMigrator();
$migrator->migrate();
