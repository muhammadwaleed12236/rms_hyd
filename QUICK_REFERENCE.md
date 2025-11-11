# Restaurant Management System - Quick Reference

## 🚀 Quick Start Commands

```bash
# Setup
composer install
npm install
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan storage:link

# Development
php artisan serve          # Terminal 1
npm run dev               # Terminal 2

# Production
npm run build
php artisan optimize
```

---

## 🔄 API Endpoints Cheat Sheet

### Categories
```
GET    /api/categories
POST   /categories              { name: string }
PUT    /categories/{id}         { name: string }
DELETE /categories/{id}
```

### Subcategories
```
GET    /api/subcategories
POST   /subcategories           { name: string, category_id: int }
PUT    /subcategories/{id}      { name: string, category_id: int }
DELETE /subcategories/{id}
```

### Items
```
GET    /items
GET    /items/{id}
POST   /items                   { name, image?, subcategory_id, sizes[] }
PUT    /items/{id}              { name, image?, subcategory_id, sizes[] }
DELETE /items/{id}
```

### Sales
```
GET    /sales
POST   /sales                   { type, total, cart[], customer_* }
GET    /sales/{id}
```

### Expenses
```
GET    /expenses
POST   /expenses                { title, amount }
PUT    /expenses/{id}           { title, amount }
DELETE /expenses/{id}
```

---

## 📊 Database Models

### Category
- id, name, timestamps
- hasMany: Subcategories

### Subcategory
- id, name, category_id, timestamps
- belongsTo: Category
- hasMany: Items

### Item
- id, name, image, subcategory_id, timestamps
- belongsTo: Subcategory
- hasMany: Sizes

### Size
- id, item_id, size, price, timestamps
- belongsTo: Item

### Sale
- id, type, total, cart (JSON), customer_name, customer_phone, customer_address, timestamps

### Expense
- id, title, amount, timestamps

---

## 🎨 Component Classes

### Modals
- `.modal-backdrop` - Animated background
- `.modal-content` - Animated content box
- `.closing` - Exit animation class

### Lists
- `.item-card` - Card element
- `.removing` - Remove animation

### Utilities
- `.cursor-pointer` - Interactive element
- `.transition-all` - Smooth transitions
- `.toast` - Notification

---

## 🔑 Key Features Summary

| Page | Add | Edit | Delete | Features |
|------|-----|------|--------|----------|
| Categories | ✅ | ✅ | ✅ | Real-time, Toast, Animations |
| Subcategories | ✅ | ✅ | ✅ | Category linked, AJAX |
| Items | ✅ | ✅ | ✅ | Images, Multiple sizes, Preview |
| Sales | - | - | - | Cart, 3 Types, Delivery form |
| Expenses | ✅ | ✅ | ✅ | Statistics, Date tracking |

---

## 💾 File Upload

```javascript
// Handling file upload
const formData = new FormData();
formData.append('image', file);
formData.append('name', 'Item name');
formData.append('sizes', JSON.stringify(sizes));

fetch('/items', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': token },
    body: formData
});
```

---

## 🎯 Validation Rules

### Items
- name: required, string, max:255
- image: nullable, image, mimes:jpeg,png,jpg,gif, max:2048
- sizes: required, array
- sizes.*.size: required, string, max:50
- sizes.*.price: required, numeric, min:0

### Sales
- type: required, in:dine_in,take_away,delivery
- cart: required, array, min:1
- customer_*: required for delivery type

### Expenses
- title: required, string, max:255
- amount: required, numeric, min:0

---

## 🎨 Color Palette

- **Primary**: `#4F46E5` (indigo-600)
- **Hover**: `#4338CA` (indigo-700)
- **Success**: `#10B981` (green-600)
- **Warning**: `#F59E0B` (yellow-500)
- **Danger**: `#EF4444` (red-600)
- **Background**: `#F9FAFB` (gray-50)
- **Border**: `#E5E7EB` (gray-200)

---

## 📱 Responsive Breakpoints

- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px

---

## 🔒 Security Checklist

- ✅ CSRF protection on all forms
- ✅ Input validation (client & server)
- ✅ File type validation
- ✅ File size limits
- ✅ XSS prevention (HTML escaping)
- ✅ SQL injection prevention (Eloquent ORM)

---

## 📈 Performance Tips

1. Use `with()` for eager loading relationships
2. Index database columns (category_id, item_id, etc.)
3. Minify assets with `npm run build`
4. Use caching for static data
5. Implement pagination for large lists

---

## 🐛 Common Issues & Solutions

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Reset database
php artisan migrate:refresh --seed

# Fix permissions
chmod -R 775 storage/ bootstrap/cache/

# Fix storage link
php artisan storage:link
```

---

## 📞 Support

For issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Browser console: F12 → Console tab
3. Network tab: F12 → Network tab

---

**Version**: 1.0  
**Last Updated**: November 11, 2025
