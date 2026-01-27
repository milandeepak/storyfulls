# Old Site Blocks → New Site Migration Guide

## 🎯 Philosophy Change: Blocks → Paragraphs

**Old Site:** Content sections = Hard-coded blocks  
**New Site:** Content sections = Flexible Paragraphs

**Why this is better:**
- ✅ Admins can add/remove sections without developer
- ✅ Each page can have unique layout
- ✅ Drag and drop ordering
- ✅ No block configuration needed
- ✅ More flexible and maintainable

---

## 📋 Complete Migration Mapping

### **HOME PAGE CONTENT (Now Paragraphs)**

| Old Site Block | New Site Paragraph | How to Add |
|----------------|-------------------|------------|
| **Home Slider** | Hero Banner | ✅ Already on homepage |
| **Age Group Block 2** | Books Showcase (Age) | ✅ Already on homepage |
| **Best Reviewed Books** | Books Showcase (Rated) | ✅ Already on homepage |
| **Series And Inclusive Books** | Books Showcase (Inclusivity) | ✅ Already on homepage |
| **Book of the week** | Call to Action (Book of Season) | ✅ Already on homepage |
| **Announcement** | Call to Action (Join Community) | ✅ Already on homepage |
| **HomeTitlesCategoryBlock** | Books Showcase (Genres) | ✅ Already on homepage |

**To modify these:**
1. Go to: `/node/16/edit` (Homepage)
2. Scroll to "Content sections"
3. Add, remove, or reorder paragraphs
4. Save

---

### **SYSTEM BLOCKS (Still Blocks)**

| Old Block | New Block | Status | Location |
|-----------|-----------|--------|----------|
| **Site branding** | Site branding | ✅ Enabled | Header |
| **Main navigation** | Main navigation | ✅ Enabled | Primary menu |
| **User account menu** | User account menu | ✅ Enabled | Secondary menu |
| **Footer menu** | Footer menu | ✅ Created | Footer |
| **Page title** | Page title | ❌ Disabled | (Hidden) |
| **Powered by Drupal** | Powered by | ❌ Disabled | (Hidden) |
| **Breadcrumbs** | Breadcrumbs | ❌ Disabled | (Can enable if needed) |

**To manage these:**
- Go to: `/admin/structure/block`
- Select theme: **storyfulls_theme**
- Place or configure blocks

---

### **CUSTOM BLOCKS TO RECREATE**

#### **1. Search Block**
**Old:** Search Icon block  
**New:** Add search form block

```bash
# Enable search block
Go to: /admin/structure/block
Click: Place block
Find: Search form
Region: Header or Primary menu
Save
```

#### **2. User Login Block**
**Old:** Logged in User block  
**New:** User account menu (already active)

No action needed - already working!

#### **3. Social Icons / Custom Links**
**Old:** Social icons in additional navigation  
**New:** Create custom menu

```bash
# Create social media menu
Go to: /admin/structure/menu/add
Name: Social Media
Add links to Facebook, Twitter, Instagram, etc.
Place as block in header/footer
```

#### **4. Custom HTML Blocks**
**Old:** Custom blocks like excerptTurtleFarm, Managed ad  
**New:** Create custom block types or use Basic blocks

```bash
# Create custom content blocks
Go to: /block/add
Type: Basic block
Add your content
Place in desired region
```

---

### **SIDEBAR CONTENT**

Your old site had sidebar regions. The new site uses a different approach:

**Old Approach:**
- Sidebar First / Sidebar Second regions
- Blocks placed in sidebars

**New Approach:**
- Paragraphs can span full-width or have columns
- Use Featured Content paragraph for sidebar-like content
- Or create specific page layouts with sidebars if needed

**To add sidebar functionality:**
1. Create Layout Builder-enabled pages
2. Or use Paragraphs with column layouts
3. Or add sidebar regions to page templates

---

## 🔧 How to Configure Each Area

### **Header/Navigation**

**Current setup:**
- ✅ Logo/Site name (branding block)
- ✅ Main menu
- ✅ User account menu

**To customize:**
```bash
# Add logo
/admin/appearance/settings/storyfulls_theme
Upload logo image

# Edit main menu
/admin/structure/menu/manage/main
Add/remove/reorder menu items

# Edit footer menu
/admin/structure/menu/manage/footer
Add links
```

### **Footer**

**To add footer content:**
```bash
# Method 1: Use Footer menu
/admin/structure/menu/manage/footer
Add menu items

# Method 2: Place custom blocks
/admin/structure/block
Place blocks in Footer region

# Method 3: Edit page.html.twig template
Add custom HTML/content in footer section
```

---

## 📚 Content Type Specific Blocks

### **Book-Related Blocks**

| Old Block | New Implementation |
|-----------|-------------------|
| **Age Group filtering** | Books Showcase paragraph with age selectors |
| **Book search** | Use Views + Search API (or simple search form) |
| **Series books** | Books Showcase paragraph filtered by series |
| **Inclusive books** | Books Showcase paragraph with inclusivity filter |
| **Book of the week** | CTA paragraph with featured book |

### **Blog/Event Blocks**

| Old Block | New Implementation |
|-----------|-------------------|
| **Recent blogs** | Featured Content paragraph |
| **Upcoming events** | Featured Content paragraph |
| **Young Readers/Writers** | Featured Content paragraph with cards |

---

## 🚀 Step-by-Step: Adding Content Like Old Site

### **Example: Add "Book of the Month" Section**

**Old way:** Create custom block, place in region  
**New way:** Add CTA paragraph

1. Go to `/node/16/edit` (Homepage)
2. Scroll to Content sections
3. Click "Add Call to Action"
4. Fill in:
   - Heading: "Book of the Month"
   - Body: Book description
   - Button: "Read More"
   - Link: `/node/BOOK_ID`
   - Background: Light
5. Save

Done! No block configuration, no region setup!

### **Example: Add Search Functionality**

```bash
# Option 1: Enable search block
1. Go to /admin/structure/block
2. Click "Place block"
3. Find "Search form"
4. Region: Header
5. Save

# Option 2: Create custom search page
1. Create View of Books
2. Add exposed filter
3. Add search field
4. Display as page
5. Link from menu
```

---

## ⚙️ Block vs Paragraph Decision Tree

**Use BLOCKS for:**
- ✅ Navigation menus
- ✅ Search forms
- ✅ User account links
- ✅ Site branding/logo
- ✅ Admin tools
- ✅ System messages

**Use PARAGRAPHS for:**
- ✅ Homepage sections
- ✅ Content showcases
- ✅ Hero banners
- ✅ Call-to-action sections
- ✅ Featured content
- ✅ Dynamic content areas

**Rule of thumb:**  
- If it changes per-page → Paragraph  
- If it's site-wide navigation/tool → Block

---

## 📊 Current Block Setup Summary

### **Active Blocks:**
1. ✅ Site branding (Header)
2. ✅ Main navigation (Primary menu)
3. ✅ User account menu (Secondary menu)
4. ✅ Main page content (Content region)
5. ✅ Footer menu (Footer)

### **Disabled Blocks:**
- ❌ Page title (not needed for homepage)
- ❌ Powered by Drupal (removed for cleaner look)
- ❌ Breadcrumbs (can enable if needed)
- ❌ Search forms (can enable if needed)
- ❌ Admin tabs (hidden from public)

### **Replaced by Paragraphs:**
- All homepage content sections
- Featured content areas
- Call-to-action sections
- Book showcases
- Hero banners

---

## 🎨 Next Steps

### **1. Configure Menus**
```bash
# Main navigation
/admin/structure/menu/manage/main
Add: Home, Books, Blogs, Events, About, Contact

# Footer menu
/admin/structure/menu/manage/footer
Add: Privacy, Terms, Contact, About
```

### **2. Add Logo**
```bash
/admin/appearance/settings/storyfulls_theme
Upload your Storyfulls logo
```

### **3. Enable Additional Blocks (if needed)**
```bash
/admin/structure/block
Enable: Search, Breadcrumbs, or custom blocks
```

### **4. Create Custom Blocks (if needed)**
```bash
/block/add/basic
Create: Social media links, Newsletter signup, etc.
```

---

## ✨ Summary

**You now have:**
- ✅ Flexible paragraph-based homepage
- ✅ Essential navigation blocks
- ✅ Clean, modern architecture
- ✅ Better than old site's rigid block layout

**Old site:** 30+ blocks, complex configuration  
**New site:** 5 essential blocks + flexible paragraphs

**Result:** Much easier for admins to manage! 🎉

---

## 🆘 Need Help?

**To add specific functionality from old site:**
1. Identify the block name
2. Check this document for mapping
3. Either add as paragraph or enable block
4. Configure as needed

**Common tasks:**
- Add content section → Add paragraph to homepage
- Add navigation item → Edit main menu
- Add footer link → Edit footer menu
- Add system feature → Enable block

Your new site is more flexible and easier to manage! 🚀
