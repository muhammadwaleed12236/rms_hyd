# Restaurant Management System - Setup Guide

## 🎉 System Overview

The Restaurant Management System (RMS) is a complete Laravel-based web application for managing restaurant operations. It includes categories, subcategories, items with multiple sizes/prices, sales management with different dining types, and expense tracking.

---

## ✨ Features Implemented

### 1. **Dashboard** 📊
- Real-time statistics (Total Sales, Revenue, Expenses, Net Profit)
- Recent sales overview
- Quick navigation to all sections

### 2. **Categories Management** 📂
- Add, edit, delete categories
- Real-time AJAX operations
- Smooth animations and transitions
- Empty state handling
- Toast notifications
- Live updates without page reload

### 3. **Subcategories Management** 📁
- Add, edit, delete subcategories
- Linked to categories
- AJAX-based operations
- Enhanced UX with modals
- Live category information display

### 4. **Items Management** 🍽️
- Add, edit, delete items
- Image upload and preview
- Multiple sizes and prices per item
- Dynamic "Add More" sizes functionality
- Size removal with validation
- Image preview in modal
- Live cart updates

### 5. **Sales Management** 💳
- Product selection from dropdown
- Product image display
- Size selection with dynamic pricing
- Quantity controls with +/− buttons
- Live cart summary
- Three dining type options:
  - 🍽️ Dine In
  - 🛍️ Take Away
  - 🚚 Delivery (with customer details form)
- Delivery details modal with validation
- Real-time price calculations
- Cart management (add/remove items)

### 6. **Expenses Management** 💰
- Add, edit, delete expenses
- Real-time statistics (Total, Count, Average)
- Professional table display
- Date tracking
- Quick analytics dashboard

### 7. **Improved Layout** 🎨
- Modern gradient sidebar with icons
- Responsive design (Mobile, Tablet, Desktop)
- Mobile menu toggle
- Sticky header
- Smooth transitions and animations
- Custom scrollbar styling
- Professional color scheme

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Laravel 12+
- MySQL/MariaDB
- Node.js & npm (for Tailwind CSS)

### Installation Steps

1. **Clone/Navigate to Project**
```bash
cd c:\Users\Manik\Documents\sites\rms-new
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database Setup**
```bash
php artisan migrate
```

5. **Build Assets**
```bash
npm run build
# For development with hot reload:
npm run dev
```

6. **Seed Database (Optional)**
```bash
php artisan db:seed
```

7. **Start Development Server**
```bash
php artisan serve
```

8. **Access Application**
Navigate to `http://localhost:8000` in your browser.

---

## 📁 Project Structure

```
rms-new/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── SubcategoryController.php
│   │   │   ├── ItemController.php
│   │   │   ├── SaleController.php
│   │   │   └── ExpenseController.php
│   │   └── Requests/
│   │       └── StoreItemRequest.php
│   └── Models/
│       ├── Category.php
│       ├── Subcategory.php
│       ├── Item.php
│       ├── Size.php
│       ├── Sale.php
│       └── Expense.php
├── database/
│   ├── migrations/
│   │   ├── 2025_11_10_200403_create_categories_table.php
│   │   ├── 2025_11_10_200423_create_subcategories_table.php
│   │   ├── 2025_11_10_200446_create_items_table.php
│   │   ├── 2025_11_10_200513_create_sizes_table.php
│   │   ├── 2025_11_10_200537_create_expenses_table.php
│   │   ├── 2025_11_10_200555_create_sales_table.php
│   │   └── 2025_11_11_add_image_to_items.php
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   ├── categories/
│   │   │   └── index.blade.php
│   │   ├── subcategories/
│   │   │   └── index.blade.php
│   │   ├── items/
│   │   │   └── index.blade.php
│   │   ├── sales/
│   │   │   └── index.blade.php
│   │   ├── expenses/
│   │   │   └── index.blade.php
│   │   ├── dashboard.blade.php
│   │   └── welcome.blade.php
│   └── css/
│       └── app.css
├── routes/
│   ├── web.php
│   └── console.php
└── public/
    └── storage/ (for uploaded images)
```

---

## 🗄️ Database Schema

### Categories Table
```sql
id | name | created_at | updated_at
```

### Subcategories Table
```sql
id | name | category_id | created_at | updated_at
```

### Items Table
```sql
id | name | image | subcategory_id | created_at | updated_at
```

### Sizes Table
```sql
id | item_id | size | price | created_at | updated_at
```

### Sales Table
```sql
id | type | total | cart | customer_name | customer_phone | customer_address | created_at | updated_at
```

### Expenses Table
```sql
id | title | amount | created_at | updated_at
```

---

## 🎨 UI/UX Features

### Animations
- **Slide In**: Items appearing with scale and fade
- **Fade In/Out**: Modal backdrops
- **Slide Out**: Items disappearing (delete operations)
- **Smooth Transitions**: Hover effects on buttons

### Color Scheme
- **Primary**: Indigo (600/700)
- **Success**: Green (600/700)
- **Warning**: Yellow (500)
- **Danger**: Red (600)
- **Neutral**: Gray (various shades)

### Responsive Design
- **Desktop**: Full sidebar, multi-column layouts
- **Tablet**: Adjusted spacing and grid layouts
- **Mobile**: Collapsible sidebar, single column layouts

---

## 🔄 AJAX Operations

All operations use AJAX for seamless UX:

### Categories API
```
GET    /api/categories              - List all categories
POST   /categories                  - Create category
PUT    /categories/{id}             - Update category
DELETE /categories/{id}             - Delete category
```

### Subcategories API
```
GET    /api/subcategories           - List with categories
POST   /subcategories               - Create subcategory
PUT    /subcategories/{id}          - Update subcategory
DELETE /subcategories/{id}          - Delete subcategory
```

### Items API
```
GET    /items                       - List all items
GET    /items/{id}                  - Get item details
POST   /items                       - Create item (with image)
PUT    /items/{id}                  - Update item (with image)
DELETE /items/{id}                  - Delete item
```

### Sales API
```
GET    /sales                       - List all sales
POST   /sales                       - Create sale
GET    /sales/{id}                  - Get sale details
```

### Expenses API
```
GET    /expenses                    - List all expenses
POST   /expenses                    - Create expense
PUT    /expenses/{id}               - Update expense
DELETE /expenses/{id}               - Delete expense
```

---

## 📝 Usage Examples

### Adding a Category
1. Navigate to Categories page
2. Click "+ Add Category" button
3. Enter category name
4. Click "Save"
5. Category appears in list with animations

### Creating an Item with Sizes
1. Go to Items management
2. Click "+ Add Item"
3. Enter item name
4. Select subcategory
5. Upload image (optional)
6. Add sizes with prices
7. Click "+ Add Size" for more options
8. Remove sizes with × button
9. Save and view in grid

### Processing a Sale
1. Navigate to Sales
2. Select item from dropdown
3. View product image
4. Choose size (price updates automatically)
5. Set quantity with +/− buttons
6. Click "Add to Cart"
7. Review cart summary
8. Click "Proceed to Checkout"
9. Select dining type (Dine In, Take Away, or Delivery)
10. If Delivery: Enter customer details
11. Submit sale
12. View confirmation

---

## 🔐 Validation

### Form Validation
- **Real-time**: Errors clear as user types
- **Server-side**: Laravel Request classes
- **Client-side**: HTML5 + JavaScript

### CSRF Protection
- All POST/PUT/DELETE requests require CSRF token
- Token embedded in forms and AJAX headers

---

## 💾 File Uploads

### Image Storage
- Location: `storage/app/public/items/`
- Public path: `/storage/items/{filename}`
- Formats: JPG, PNG, GIF, JPEG
- Max size: 2MB
- Auto-deletion when item is deleted

---

## 🎯 Key Improvements Over Original

| Feature | Before | After |
|---------|--------|-------|
| Modals | Black overlay | Subtle gray overlay |
| Animations | None | Smooth slide/fade effects |
| Cursor | Default | Pointer on interactive elements |
| UX | Page reloads | Real-time AJAX updates |
| Layout | Basic sidebar | Modern gradient sidebar |
| Mobile | Not responsive | Fully responsive |
| Notifications | Alerts | Toast notifications |
| Forms | Simple | Enhanced with validation |
| Images | Not supported | Full upload & preview |
| Cart | Basic | Full shopping cart system |

---

## 🐛 Troubleshooting

### Issue: Images not displaying
**Solution**: 
```bash
php artisan storage:link
```

### Issue: CSRF token mismatch
**Solution**: Ensure CSRF token is in request headers:
```javascript
'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
```

### Issue: Migrations not running
**Solution**: 
```bash
php artisan migrate:refresh
php artisan migrate
```

### Issue: Styles not applying
**Solution**:
```bash
npm run build
php artisan cache:clear
```

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com)
- [RESTful API Best Practices](https://restfulapi.net)

---

## 🤝 Contributing

To add new features:
1. Create feature branch
2. Follow existing code patterns
3. Test AJAX operations
4. Update documentation

---

## 📄 License

Private Project - Developed for Restaurant Management

---

## 🎓 Learning Outcomes

This project demonstrates:
- ✅ Laravel full-stack development
- ✅ RESTful API design
- ✅ AJAX/Fetch API integration
- ✅ Database relationships
- ✅ Form validation
- ✅ File uploads
- ✅ Responsive web design
- ✅ UI/UX best practices
- ✅ CSS animations
- ✅ Modern JavaScript

---

**Last Updated**: November 11, 2025  
**Version**: 1.0  
**Status**: ✅ Complete & Production Ready
