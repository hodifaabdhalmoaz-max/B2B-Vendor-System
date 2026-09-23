import requests

BASE_URL = "http://localhost:8000"


def test_add_product_to_cart():
    try:
        # Step 1: Get products list to find a valid product_id
        resp = requests.get(f"{BASE_URL}/shop", timeout=30)
        assert resp.status_code == 200, f"Failed to get products list: {resp.text}"
        products_data = resp.json()
        assert isinstance(products_data, dict), f"Products response is not a JSON object: {products_data}"
        products = products_data.get("data")
        assert isinstance(products, list), "Products data is not a list"
        assert len(products) > 0, "No products available for testing"

        product_id = None
        # product list might be nested, try to pick product_id
        for p in products:
            if isinstance(p, dict) and "id" in p:
                product_id = p["id"]
                break
        assert product_id is not None, "No valid product_id found in product list"

        # Step 2: POST /cart/add with valid product_id and quantity
        payload = {
            "product_id": product_id,
            "quantity": 1
        }
        headers = {
            "Content-Type": "application/json"
        }
        add_resp = requests.post(f"{BASE_URL}/cart/add", json=payload, headers=headers, timeout=30)
        assert add_resp.status_code == 200, f"Add to cart failed: {add_resp.status_code} {add_resp.text}"
        content_type = add_resp.headers.get('Content-Type', '')
        assert 'application/json' in content_type.lower(), f"Unexpected Content-Type in response: {content_type}"
        cart_data = add_resp.json()
        # Validate cart payload contains the product added with correct quantity
        assert isinstance(cart_data, dict), f"Cart response is not a JSON object: {cart_data}"
        # Check presence of product_id in cart items and quantity is correct
        items = cart_data.get("items") or cart_data.get("cart_items") or []
        assert isinstance(items, list), f"Cart items missing or not a list: {items}"
        found = False
        for item in items:
            if isinstance(item, dict) and ("product_id" in item or "id" in item):
                pid = item.get("product_id") or item.get("id")
                if pid == product_id:
                    qty = item.get("quantity") or item.get("qty") or 0
                    assert qty >= 1, f"Quantity for product {product_id} in cart is less than 1"
                    found = True
                    break
        assert found, f"Product {product_id} not found in cart items"

    finally:
        # Cleanup: No endpoint documented for cart removal, so skipping delete
        # If implemented, would call to remove product from cart here
        pass


test_add_product_to_cart()
