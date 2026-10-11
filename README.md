# CraveHub - Online Food Delivery System (PHP & MongoDB)

A full-stack, enterprise-grade Online Food Delivery Web Application built with **PHP 8.2**, **MongoDB**, Vanilla CSS, and modern interactive JavaScript.

---

## 🌟 Key Features

### 👤 Customer Experience
1. **Explore & Browse**:
   - Modern Swiggy/Zomato-inspired Dark Glassmorphism UI.
   - 20+ Pre-populated dishes across 7 categories (Pizzas, Burgers, Biryani, North Indian, Asian Momos, Desserts, Beverages).
   - Real-time search by dish title, category, or ingredients.
   - Veg / Non-Veg filters, spicy level badges, bestseller tags, and preparation time counters.

2. **Interactive Cart & Coupons**:
   - Slide-out Cart Drawer accessible on any page + Dedicated Full Cart page.
   - Instant quantity adjustments (+ / -) and item removal via AJAX.
   - Promo discount coupons (`TASTY50`, `CRAVE100`, `FREEDEL`, `FIRSTBITE`) with automated calculation and minimum-order validation.
   - Dynamic Bill breakdown with Subtotal, Delivery Fee (Free above ₹350), 5% GST, and Grand Total.

3. **Multi-Channel Dummy Payment Simulation**:
   - **UPI / QR Code**: Dynamic QR code with live radar scanner animation, payment app buttons (Google Pay, PhonePe, Paytm).
   - **Credit / Debit Card**: Interactive 3D credit card preview that dynamically reflects card number, cardholder name, and expiry date as the user types.
   - **Net Banking**: Instant simulation for major banks (HDFC, SBI, ICICI, Axis).
   - **Cash on Delivery (COD)**: Verified 1-click cash order option.
   - Animated payment authorization gateway modal with progress bar and bank verification feedback.

4. **Live Real-Time Order Tracking**:
   - 5-Stage Animated Timeline:
     `Order Placed` ➔ `Kitchen Confirmed` ➔ `Food Cooking` ➔ `Out for Delivery` ➔ `Delivered`.
   - Simulated Live GPS Map with animated valet transit radar and distance countdown.
   - Assigned Delivery Partner profile with photo, name, rating, vehicle number, and call button.
   - **"Advance Status (Demo Step)"** button: Click to instantaneously advance order stages during project presentations!

5. **Customer Authentication & Profile**:
   - User Sign Up with bcrypt password hashing and email verification.
   - User Login with 1-click demo login buttons.
   - Customer Profile with saved default delivery address.
   - Complete Order History with 1-click **Re-Order** button and invoice view.

---

### 🛡️ Admin Management Panel
Accessible at: `http://localhost:8000/admin/`

1. **Dashboard Analytics**:
   - Metric cards: Lifetime Net Revenue, Total Orders, Total Food Dishes, Active Customers.
   - Alert banner for pending orders needing kitchen attention.
   - Recent orders table with instant status updater dropdown.

2. **Food Menu Management (CRUD)**:
   - Add new dishes with photos, categories, prices, prep times, spicy levels, and descriptions.
   - Edit existing dish pricing and details in modal.
   - Immediate stock toggle switch (In Stock / Sold Out).
   - Delete dishes with confirmation dialog.

3. **Order Management**:
   - Filter orders by status (All, Placed, Confirmed, Preparing, Out for Delivery, Delivered, Cancelled).
   - Search by Order ID, customer name, or phone number.
   - Instant live status updater dropdown (changes are instantly visible to the customer on their live tracking page).

4. **Customer Directory**:
   - View all registered customers from MongoDB.
   - Track order counts and lifetime money spent by each customer.
   - Direct button to view all orders placed by any specific customer.

5. **Promo Codes & Coupons**:
   - Create percentage, flat discount, or free delivery coupons with minimum order thresholds.

---

## 🛠️ Technology Stack & Architecture

- **Backend**: PHP 8.2 (Modular procedural + OOP database layer).
- **Database**: **MongoDB** (`mongodb://127.0.0.1:27017` database `food_delivery_db`).
  - Native `MongoDB\Driver\Manager` with native BSON ObjectIds.
  - Built-in dual-engine fail-safe storage: When MongoDB is running, it connects and operates directly on MongoDB; if mongod is stopped or in development, it automatically operates through the local JSON NoSQL sync engine without crashing.
- **Frontend**: Vanilla CSS, HTML5, FontAwesome 6, Google Fonts (Outfit & Plus Jakarta Sans), Vanilla JavaScript & AJAX.

---

## 🚀 How to Run the Project

1. **Clone the repository:**
```bash
git clone https://github.com/hey-akhil/CraveHub.git
cd CraveHub
```

2. **Start the PHP Built-in Server:**
```bash
php -S 127.0.0.1:8000
```

3. **Open in your browser:**
- **Customer Storefront**: [http://localhost:8000](http://localhost:8000)
- **Admin Portal**: [http://localhost:8000/admin](http://localhost:8000/admin)

---

## ☁️ Deploying on Render (Free)

This repository includes a production-ready `Dockerfile` and `render.yaml` configured for Render:

1. Go to [Render.com](https://render.com) and click **New +** ➔ **Web Service**.
2. Connect your GitHub repository: `https://github.com/hey-akhil/CraveHub`.
3. Render will automatically detect the **Docker** environment.
4. (Optional for live MongoDB Atlas cloud database):
   - In **Environment Variables**, add:
     - `MONGODB_URI`: `mongodb+srv://<username>:<password>@cluster.mongodb.net/?retryWrites=true&w=majority`
     - `MONGODB_DB`: `food_delivery_db`
5. Click **Deploy Web Service** — Render will build and launch your site with a live HTTPS URL!

---

## 🔑 Demo Login Credentials

### Customer Login
- **Email**: `rahul@gmail.com`
- **Password**: `user123`
*(Or click the "Customer Auto-fill" button on the login screen)*

### Admin Login
- **Email**: `admin@cravehub.com`
- **Password**: `admin123`
*(Or click the "Auto-fill" button on the admin login screen)*
