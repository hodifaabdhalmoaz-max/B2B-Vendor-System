import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_admin_product_management():
    admin_login_url = f"{BASE_URL}/admin/login"
    product_store_url = f"{BASE_URL}/admin/product/store"
    product_list_url = f"{BASE_URL}/admin/products"

    admin_credentials = {
        "email": "admin@example.com",
        "password": "adminpassword"
    }

    valid_product_payload = {
        "name": "Test Product",
        "description": "A product created during testing.",
        "price": 99.99,
        "stock": 10,
        "category_id": 1,  # assuming valid existing category id
        "brand_id": 1      # assuming valid existing brand id
    }

    invalid_product_payload_missing_fields = {
        # Missing required fields like name and price
        "description": "Invalid product with missing fields"
    }

    # Step 1: Admin login
    resp_login = requests.post(admin_login_url, json=admin_credentials, timeout=TIMEOUT)
    assert resp_login.status_code == 200, f"Admin login failed: {resp_login.status_code}, {resp_login.text}"
    jwt_token = resp_login.json().get("token") or resp_login.json().get("access_token")
    assert jwt_token, "JWT token missing in login response"

    headers_auth = {
        "Authorization": f"Bearer {jwt_token}",
        "Content-Type": "application/json"
    }

    product_id = None
    # Step 2: Create a product with valid data
    try:
        resp_create = requests.post(product_store_url, json=valid_product_payload, headers=headers_auth, timeout=TIMEOUT)
        assert resp_create.status_code == 201, f"Product creation failed: {resp_create.status_code}, {resp_create.text}"
        resp_create_json = resp_create.json()
        # Extract product ID for cleanup and verification
        product_id = resp_create_json.get("id") or resp_create_json.get("data", {}).get("id")
        assert product_id, "Created product ID is missing"

        # Step 3: Retrieve product list and verify the new product is included
        resp_list = requests.get(product_list_url, headers=headers_auth, timeout=TIMEOUT)
        assert resp_list.status_code == 200, f"Get products failed: {resp_list.status_code}, {resp_list.text}"
        products = resp_list.json()
        # products might be a list or dictionary with "data" key
        product_items = products if isinstance(products, list) else products.get("data", [])
        assert any(item.get("id") == product_id for item in product_items), "Created product not found in product list"

        # Step 4: Attempt create product with missing required fields (should get 422)
        resp_invalid = requests.post(product_store_url, json=invalid_product_payload_missing_fields, headers=headers_auth, timeout=TIMEOUT)
        assert resp_invalid.status_code == 422, f"Expected validation error 422 but got {resp_invalid.status_code}"

        # Step 5: Access products endpoint with expired JWT (simulate by waiting or using a dummy expired token)
        expired_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.ExpiredTokenSample"
        headers_expired = {
            "Authorization": f"Bearer {expired_token}",
            "Content-Type": "application/json"
        }
        resp_expired = requests.get(product_list_url, headers=headers_expired, timeout=TIMEOUT)
        assert resp_expired.status_code == 401, f"Expected 401 Unauthorized for expired token but got {resp_expired.status_code}"

    finally:
        # Cleanup: delete the created product if product_id is available
        if product_id:
            delete_url = f"{product_store_url}/{product_id}"
            try:
                # Assuming product deletion via DELETE method
                resp_delete = requests.delete(delete_url, headers=headers_auth, timeout=TIMEOUT)
                # Deletion might return 200 or 204 for success or 404 if already deleted
                assert resp_delete.status_code in [200, 204, 404], f"Product deletion failed: {resp_delete.status_code}"
            except Exception:
                pass

test_admin_product_management()