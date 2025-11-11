# Dropdown Implementation Guide

## Overview
This document details how the category and subcategory dropdowns are implemented in the RMS system.

---

## 1. Subcategories Page - Category Dropdown

### File Location
`resources/views/subcategories/index.blade.php`

### HTML Structure
```html
<div>
    <label for="categoryInput" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
    <select 
        id="categoryInput" 
        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
        required>
        <option value="">Select a category</option>
    </select>
    <div id="categoryError" class="text-red-500 text-sm mt-1 hidden"></div>
</div>
```

### JavaScript Implementation
```javascript
// Load categories from API
const loadCategories = async () => {
    try {
        const response = await fetch('/api/categories');
        const categories = await response.json();
        categoryInput.innerHTML = '<option value="">Select a category</option>';
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categoryInput.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading categories:', error);
    }
};

// Call on page load
loadCategories();

// Validate on change
categoryInput.addEventListener('change', () => {
    if (!categoryError.classList.contains('hidden')) {
        validateInput();
    }
});
```

### Validation
```javascript
const validateInput = () => {
    const category = categoryInput.value;
    
    if (!category) {
        categoryError.textContent = 'Please select a category.';
        categoryError.classList.remove('hidden');
        categoryInput.classList.add('border-red-500');
        return false;
    } else {
        categoryError.classList.add('hidden');
        categoryInput.classList.remove('border-red-500');
        return true;
    }
};
```

### Form Submission
```javascript
const name = nameInput.value.trim();
const categoryId = categoryInput.value;

const response = await fetch(url, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    body: JSON.stringify({ name, category_id: categoryId }),
});
```

---

## 2. Items Page - Subcategory Dropdown

### File Location
`resources/views/items/index.blade.php`

### HTML Structure
```html
<div>
    <label for="subcategoryInput" class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
    <select 
        id="subcategoryInput" 
        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
        required>
        <option value="">Select a subcategory</option>
    </select>
    <div id="subcategoryError" class="text-red-500 text-sm mt-1 hidden"></div>
</div>
```

### JavaScript Implementation
```javascript
// Load subcategories from API
const loadSubcategories = async () => {
    try {
        const response = await fetch('/api/subcategories');
        const subcategories = await response.json();
        subcategoryInput.innerHTML = '<option value="">Select a subcategory</option>';
        subcategories.forEach(subcategory => {
            const option = document.createElement('option');
            option.value = subcategory.id;
            // Display format: "Subcategory (Category)"
            option.textContent = `${subcategory.name} (${subcategory.category.name})`;
            subcategoryInput.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading subcategories:', error);
    }
};

// Call on page load
loadSubcategories();

// Validate on change
subcategoryInput.addEventListener('change', () => {
    if (!subcategoryError.classList.contains('hidden')) {
        validateInput();
    }
});
```

### Validation
```javascript
const validateInput = () => {
    const subcategory = subcategoryInput.value;
    
    if (!subcategory) {
        subcategoryError.textContent = 'Please select a subcategory.';
        subcategoryError.classList.remove('hidden');
        subcategoryInput.classList.add('border-red-500');
        return false;
    } else {
        subcategoryError.classList.add('hidden');
        subcategoryInput.classList.remove('border-red-500');
        return true;
    }
};
```

### Form Submission
```javascript
const formData = new FormData();
formData.append('name', nameInput.value.trim());
formData.append('subcategory_id', subcategoryInput.value);

// Add image if provided
if (imageInput.files && imageInput.files[0]) {
    formData.append('image', imageInput.files[0]);
}

// Add sizes
const sizes = [];
const sizeInputs = sizesContainer.querySelectorAll('.size-input');
const priceInputs = sizesContainer.querySelectorAll('.price-input');
sizeInputs.forEach((input, index) => {
    if (input.value.trim() && priceInputs[index].value) {
        sizes.push({
            size: input.value.trim(),
            price: parseFloat(priceInputs[index].value)
        });
    }
});

formData.append('sizes', JSON.stringify(sizes));

const response = await fetch(url, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
    },
    body: formData,
});
```

---

## 3. API Endpoints

### Categories Endpoint
**URL**: `/api/categories`  
**Method**: `GET`  
**Returns**: JSON array of all categories

```json
[
    {
        "id": 1,
        "name": "Pizza",
        "created_at": "2025-11-10T20:34:48.000000Z",
        "updated_at": "2025-11-10T20:34:48.000000Z"
    },
    {
        "id": 2,
        "name": "Burgers",
        "created_at": "2025-11-10T20:35:00.000000Z",
        "updated_at": "2025-11-10T20:35:00.000000Z"
    }
]
```

### Subcategories Endpoint
**URL**: `/api/subcategories`  
**Method**: `GET`  
**Returns**: JSON array of all subcategories with category details

```json
[
    {
        "id": 1,
        "name": "Cheese Pizza",
        "category_id": 1,
        "created_at": "2025-11-10T20:47:20.000000Z",
        "updated_at": "2025-11-10T20:47:20.000000Z",
        "category": {
            "id": 1,
            "name": "Pizza",
            "created_at": "2025-11-10T20:34:48.000000Z",
            "updated_at": "2025-11-10T20:34:48.000000Z"
        }
    },
    {
        "id": 2,
        "name": "Veggie Pizza",
        "category_id": 1,
        "created_at": "2025-11-10T20:48:00.000000Z",
        "updated_at": "2025-11-10T20:48:00.000000Z",
        "category": {
            "id": 1,
            "name": "Pizza",
            "created_at": "2025-11-10T20:34:48.000000Z",
            "updated_at": "2025-11-10T20:34:48.000000Z"
        }
    }
]
```

---

## 4. Route Configuration

### File: `routes/web.php`

```php
// API Routes
Route::prefix('api')->group(function () {
    Route::get('categories', function () {
        return response()->json(\App\Models\Category::all());
    });
    Route::get('subcategories', function () {
        return response()->json(\App\Models\Subcategory::with('category')->get());
    });
});
```

---

## 5. Database Relationships

### Category Model
```php
class Category extends Model
{
    protected $fillable = ['name'];
    
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }
}
```

### Subcategory Model
```php
class Subcategory extends Model
{
    protected $fillable = ['name', 'category_id'];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
```

### Item Model
```php
class Item extends Model
{
    protected $fillable = ['name', 'image', 'subcategory_id'];
    
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
    
    public function sizes()
    {
        return $this->hasMany(Size::class);
    }
}
```

---

## 6. Error Handling

### Network Error Handling
```javascript
const loadCategories = async () => {
    try {
        const response = await fetch('/api/categories');
        const categories = await response.json();
        // ... populate dropdown
    } catch (error) {
        console.error('Error loading categories:', error);
        // Show fallback or error message
    }
};
```

### Validation Error Handling
```javascript
if (!response.ok) {
    const data = await response.json();
    throw new Error(data.message || 'Something went wrong!');
}
```

### Form Validation
```javascript
const validateInput = () => {
    const name = nameInput.value.trim();
    const category = categoryInput.value;
    let isValid = true;

    if (!name) {
        nameError.textContent = 'Please enter a name.';
        nameError.classList.remove('hidden');
        isValid = false;
    } else {
        nameError.classList.add('hidden');
    }

    if (!category) {
        categoryError.textContent = 'Please select a category.';
        categoryError.classList.remove('hidden');
        isValid = false;
    } else {
        categoryError.classList.add('hidden');
    }

    return isValid;
};
```

---

## 7. Styling

### Dropdown Styling
```css
select {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.75rem;
    
    /* Focus state */
    focus:ring-2;
    focus:ring-blue-500;
    focus:border-transparent;
    focus:outline-none;
    
    /* Transition */
    transition: all 150ms ease;
}

/* Error state */
.border-red-500 {
    border-color: #ef4444;
}

/* Error message */
.text-red-500 {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
```

---

## 8. Accessibility Features

### Label Association
```html
<label for="categoryInput">Category</label>
<select id="categoryInput" required></select>
```

### Required Indicator
```html
<select required>
    <option value="">Select a category</option>
</select>
```

### Error Messages
```html
<div id="categoryError" class="text-red-500 text-sm mt-1 hidden"></div>
```

---

## 9. Performance Optimization

### Lazy Loading (On Page Load)
```javascript
// Load immediately when page loads
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadSubcategories();
});
```

### Caching Strategy
- Dropdowns are loaded once on page load
- If need to refresh, re-call `loadCategories()` or `loadSubcategories()`

### Debouncing (If Needed)
```javascript
const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};
```

---

## 10. Testing Checklist

- [ ] Category dropdown loads on subcategories page load
- [ ] Subcategory dropdown loads on items page load
- [ ] Can add subcategory with category selection
- [ ] Can edit subcategory and change category
- [ ] Can add item with subcategory selection
- [ ] Can edit item and change subcategory
- [ ] Validation shows error if no category selected
- [ ] Validation shows error if no subcategory selected
- [ ] Real-time error clearing on input change
- [ ] API returns correct JSON format
- [ ] Dropdown displays category/subcategory names correctly
- [ ] Format "Subcategory (Category)" displays correctly on items page
- [ ] Mobile responsive dropdown behavior
- [ ] Error handling if API fails
- [ ] Performance: dropdowns load < 1 second

---

## 11. Troubleshooting

### Dropdown Not Loading
1. Check browser console for errors
2. Verify API endpoint returns data: `curl http://localhost:8000/api/categories`
3. Clear browser cache: `Ctrl+Shift+Delete`
4. Restart Laravel server: `php artisan serve`

### Options Not Displaying
1. Check if JavaScript has errors in console
2. Verify fetch request headers include `Accept: application/json`
3. Check if API is returning correct JSON format

### Validation Not Working
1. Verify validation functions are called on change events
2. Check if error elements have correct IDs
3. Ensure error clearing logic works

---

## Summary

✅ **Subcategories Page**:
- Category dropdown loaded from `/api/categories`
- Shows all available categories
- User can select category for new/edited subcategory
- Category name displayed in list

✅ **Items Page**:
- Subcategory dropdown loaded from `/api/subcategories`
- Shows all subcategories with their category names
- Format: "Subcategory (Category)"
- User can select subcategory for new/edited item

Both dropdowns include:
- Real-time validation
- Error messages
- Error clearing on input change
- Professional styling
- Mobile responsive
- AJAX submission

