import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_place_order_authenticated():
    # NOTE: Replace these values with valid credentials and product info for your test environment.
    ADMIN_LOGIN_ENDPOINT = f"{BASE_URL}/admin/login"
    CART_ADD_ENDPOINT = f"{BASE_URL}/cart/add"
    PLACE_ORDER_ENDPOINT = f"{BASE_URL}/place-an-order"
    admin_credentials = {
        "email": "admin@example.com",
        "password": "StrongAdminPassword123!"
    }
    # Sample order details for placing an order
    order_payload = {
        "shipping_address": "123 Sample Street, Cairo, Egypt",
        "payment_method": "credit_card"
    }
    # Sample product quantity to add to cart
    product_quantity = 1

    # Step 1: Admin login to obtain JWT token (assuming same token for user auth)
    token = None
    cart_product_id = None
    try:
        login_resp = requests.post(ADMIN_LOGIN_ENDPOINT, json=admin_credentials, timeout=TIMEOUT)
        assert login_resp.status_code == 200, f"Admin login failed with status {login_resp.status_code}"
        login_data = login_resp.json()
        token = login_data.get("token") or login_data.get("access_token")
        assert token, "JWT token not found in login response"

        headers_auth = {"Authorization": f"Bearer {token}"}

        # Step 2: Find or create a product to add to cart
        # To find product_id, attempt to get products (public or admin) - as admin token is available
        products_resp = requests.get(f"{BASE_URL}/admin/products", headers=headers_auth, timeout=TIMEOUT)
        assert products_resp.status_code == 200, f"Failed to get products with status {products_resp.status_code}"
        products = products_resp.json().get("data") or products_resp.json()
        assert products and isinstance(products, list), "Invalid products list in response"
        cart_product_id = products[0].get("id")
        assert cart_product_id, "No product ID found in product list"

        # Step 3: Add product to cart
        cart_add_payload = {
            "product_id": cart_product_id,
            "quantity": product_quantity
        }
        add_cart_resp = requests.post(CART_ADD_ENDPOINT, json=cart_add_payload, timeout=TIMEOUT)
        assert add_cart_resp.status_code == 200, f"Adding to cart failed with status {add_cart_resp.status_code}"

        # Step 4: Place an order with Authorization header and order details
        place_order_resp = requests.post(
            PLACE_ORDER_ENDPOINT,
            headers=headers_auth,
            json=order_payload,
            timeout=TIMEOUT
        )
        assert place_order_resp.status_code == 201, f"Expected 201 Created, got {place_order_resp.status_code}"
        order_resp_json = place_order_resp.json()
        assert "order_id" in order_resp_json, "order_id not found in response"
        assert "summary" in order_resp_json, "summary not found in response"
    finally:
        # Cleanup: Attempt to delete created order if API endpoint or method existed, 
        # but since no delete order endpoint documented, skip cleanup for order.
        # No permanent data was created except cart addition and order placement.
        pass

test_place_order_authenticated()