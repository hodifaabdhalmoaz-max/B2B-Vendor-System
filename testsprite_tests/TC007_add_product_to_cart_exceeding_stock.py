import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def test_add_product_to_cart_exceeding_stock():
    # Step 1: Get products list to find a product and its stock quantity
    try:
        response = requests.get(f"{BASE_URL}/shop", timeout=TIMEOUT)
        assert response.status_code == 200, f"Expected 200 but got {response.status_code}"
        products = response.json().get("data") or response.json()
        assert isinstance(products, list) and len(products) > 0, "No products found in /shop response"
        # Pick the first product with a stock field
        product = None
        for p in products:
            # Expect product to have id and stock info (try stock or quantity fields - common e-commerce fields)
            if (('id' in p) and ('stock' in p or 'quantity' in p)):
                product = p
                break
        assert product is not None, "No product with stock information found"
        product_id = product['id']
        stock_quantity = product.get('stock', product.get('quantity'))
        assert isinstance(stock_quantity, int), f"Stock quantity is not an int: {stock_quantity}"
        assert stock_quantity >= 0, f"Stock quantity invalid: {stock_quantity}"

        # Step 2: Attempt to add to cart with quantity exceeding stock (stock_quantity + 1)
        add_to_cart_payload = {
            "product_id": product_id,
            "quantity": stock_quantity + 1,
        }
        add_response = requests.post(f"{BASE_URL}/cart/add",
                                     json=add_to_cart_payload,
                                     timeout=TIMEOUT)

        # Step 3: Validate response status code 409 Conflict
        assert add_response.status_code == 409, f"Expected 409 Conflict but got {add_response.status_code}"

        # Step 4: Validate response body contains out-of-stock error message
        content_type = add_response.headers.get('Content-Type', '')
        assert 'application/json' in content_type, "Expected JSON response for error"
        if not add_response.content:
            assert False, "Response body is empty, expected JSON error message"
        try:
            add_resp_json = add_response.json()
        except ValueError:
            assert False, "Response is not valid JSON"
        error_message = add_resp_json.get("error") or add_resp_json.get("message") or ""
        assert error_message, "Expected error message in response body"
        lower_error = error_message.lower()
        assert "out of stock" in lower_error or "stock" in lower_error or "exceed" in lower_error, \
            f"Unexpected error message for out-of-stock: {error_message}"

    except (requests.RequestException, AssertionError) as e:
        raise AssertionError(f"Test failed: {str(e)}")


test_add_product_to_cart_exceeding_stock()
