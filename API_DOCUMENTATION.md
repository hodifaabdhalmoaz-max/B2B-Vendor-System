# 📚 **API Documentation - Laravel 11 E-commerce**

## 🌐 **Base URL**
```
http://your-domain.com/api/v1
```

## 🔑 **Authentication**
معظم endpoints تتطلب authentication. استخدم Bearer token في header:
```
Authorization: Bearer {your-token}
```

---

## 📦 **Products API**

### **GET /products**
الحصول على قائمة المنتجات مع فلترة وترقيم

**Parameters:**
- `category` (string): فلترة حسب الفئة
- `brand` (string): فلترة حسب العلامة التجارية
- `color` (string): فلترة حسب اللون
- `size` (string): فلترة حسب الحجم
- `min_price` (number): أقل سعر
- `max_price` (number): أعلى سعر
- `search` (string): البحث في الاسم والوصف
- `stock_status` (string): حالة المخزون
- `featured` (boolean): المنتجات المميزة
- `sort_by` (string): ترتيب حسب (created_at, price, name)
- `sort_direction` (string): اتجاه الترتيب (asc, desc)
- `per_page` (number): عدد العناصر في الصفحة (max: 50)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "اسم المنتج",
      "slug": "product-slug",
      "sku": "SKU123",
      "short_description": "وصف قصير",
      "regular_price": 100.00,
      "sale_price": 80.00,
      "current_price": 80.00,
      "discount_percentage": 20,
      "stock_status": "instock",
      "quantity": 50,
      "featured": true,
      "image": "http://domain.com/uploads/products/image.jpg",
      "category": {
        "id": 1,
        "name": "الفئة",
        "slug": "category-slug"
      },
      "brand": {
        "id": 1,
        "name": "العلامة التجارية",
        "slug": "brand-slug"
      }
    }
  ],
  "meta": {
    "total": 100,
    "count": 12,
    "per_page": 12,
    "current_page": 1,
    "total_pages": 9,
    "currency": "YER",
    "currency_symbol": "ر.ي"
  },
  "links": {
    "first": "http://domain.com/api/v1/products?page=1",
    "last": "http://domain.com/api/v1/products?page=9",
    "prev": null,
    "next": "http://domain.com/api/v1/products?page=2"
  }
}
```

### **GET /products/{slug}**
الحصول على تفاصيل منتج محدد

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "اسم المنتج",
    "slug": "product-slug",
    "description": "وصف مفصل للمنتج",
    "gallery": [
      "http://domain.com/uploads/products/image1.jpg",
      "http://domain.com/uploads/products/image2.jpg"
    ],
    "colors": [
      {"id": 1, "name": "أحمر", "code": "red", "hex_value": "#ff0000"}
    ],
    "sizes": [
      {"id": 1, "name": "كبير", "code": "L"}
    ],
    "reviews": [
      {
        "id": 1,
        "rating": 5,
        "title": "ممتاز",
        "comment": "منتج رائع",
        "user": {"id": 1, "name": "أحمد"}
      }
    ],
    "reviews_count": 10,
    "average_rating": 4.5
  }
}
```

### **GET /products/featured**
المنتجات المميزة

**Parameters:**
- `limit` (number): عدد المنتجات (max: 20, default: 8)

### **GET /products/latest**
أحدث المنتجات

**Parameters:**
- `limit` (number): عدد المنتجات (max: 20, default: 8)

### **GET /products/search**
البحث في المنتجات

**Parameters:**
- `q` (string, required): كلمة البحث (min: 2, max: 100)
- `per_page` (number): عدد النتائج (max: 50, default: 12)

---

## 🏷️ **Categories API**

### **GET /categories**
قائمة الفئات

### **GET /categories/{slug}**
تفاصيل فئة محددة

### **GET /categories/{slug}/products**
منتجات فئة محددة

---

## 🏢 **Brands API**

### **GET /brands**
قائمة العلامات التجارية

### **GET /brands/{slug}**
تفاصيل علامة تجارية محددة

### **GET /brands/{slug}/products**
منتجات علامة تجارية محددة

---

## 🛒 **Cart API**

### **GET /cart**
محتويات السلة

### **POST /cart/add**
إضافة منتج للسلة

**Body:**
```json
{
  "product_id": 1,
  "quantity": 2,
  "color_id": 1,
  "size_id": 1
}
```

### **PUT /cart/{rowId}**
تحديث كمية منتج في السلة

### **DELETE /cart/{rowId}**
حذف منتج من السلة

### **DELETE /cart**
تفريغ السلة

### **POST /cart/coupon**
تطبيق كوبون خصم

---

## 🔐 **Authentication API**

### **POST /register**
تسجيل مستخدم جديد

**Body:**
```json
{
  "name": "الاسم",
  "email": "email@example.com",
  "password": "password",
  "password_confirmation": "password",
  "mobile": "777123456"
}
```

### **POST /login**
تسجيل الدخول

**Body:**
```json
{
  "email": "email@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "name": "الاسم",
      "email": "email@example.com"
    },
    "token": "bearer-token-here"
  }
}
```

---

## ❤️ **Health Check API**

### **GET /health**
فحص صحة النظام

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2025-06-21T10:30:00Z",
  "checks": {
    "database": {
      "status": "healthy",
      "response_time_ms": 25.5
    },
    "cache": {
      "status": "healthy",
      "response_time_ms": 5.2
    },
    "storage": {
      "status": "healthy",
      "free_space_gb": 50.5
    }
  }
}
```

### **GET /ping**
فحص بسيط للاتصال

---

## 📊 **Rate Limiting**

جميع API endpoints محمية بـ rate limiting:
- **المستخدمين المسجلين**: 60 طلب/دقيقة
- **الزوار**: 60 طلب/دقيقة حسب IP

**Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

---

## 🚫 **Error Responses**

### **400 Bad Request**
```json
{
  "error": "Bad Request",
  "message": "Invalid input data",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### **401 Unauthorized**
```json
{
  "error": "Unauthorized",
  "message": "Authentication required"
}
```

### **404 Not Found**
```json
{
  "error": "Not Found",
  "message": "Resource not found"
}
```

### **429 Too Many Requests**
```json
{
  "error": "Rate limit exceeded",
  "message": "Too many requests. Limit: 60 per 1 minute(s)",
  "retry_after": 60
}
```

### **500 Internal Server Error**
```json
{
  "error": "Internal Server Error",
  "message": "Something went wrong"
}
```

---

## 📈 **Performance Headers**

جميع الاستجابات تتضمن performance headers:
```
X-Response-Time: 150.25ms
X-Memory-Usage: 2.5 MB
X-Query-Count: 5
X-Query-Time: 25.5ms
```

---

## 🔧 **Development Notes**

- جميع التواريخ بصيغة ISO 8601
- العملة الافتراضية: ريال يمني (YER)
- الترقيم يبدأ من الصفحة 1
- أقصى حد للعناصر في الصفحة: 50
- جميع النصوص باللغة العربية
- دعم CORS للتطبيقات الخارجية

---

**آخر تحديث**: 21 يونيو 2025  
**إصدار API**: v1.0  
**حالة التوثيق**: ✅ مكتمل
