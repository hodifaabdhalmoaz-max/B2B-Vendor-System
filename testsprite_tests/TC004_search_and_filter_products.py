import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30
HEADERS = {
    "Accept": "application/json"
}

def test_search_and_filter_products():
    # Test search by keyword 'crib'
    params = {"search": "crib"}
    response = requests.get(f"{BASE_URL}/shop", headers=HEADERS, params=params, timeout=TIMEOUT)
    assert response.status_code == 200, f"Expected 200 but got {response.status_code} for search=crib"
    content_type = response.headers.get('Content-Type', '').lower()
    assert 'application/json' in content_type, "Response Content-Type is not application/json"
    data = response.json()
    assert isinstance(data, dict), "Response JSON should be a dictionary"
    assert "data" in data or "products" in data, "Response should contain products list"

    # Test brand filtering and price range filtering
    params = {
        "brand": "brand-x",
        "price_min": 100,
        "price_max": 500
    }
    response = requests.get(f"{BASE_URL}/shop", headers=HEADERS, params=params, timeout=TIMEOUT)
    assert response.status_code == 200, f"Expected 200 but got {response.status_code} for brand and price filtering"
    content_type = response.headers.get('Content-Type', '').lower()
    assert 'application/json' in content_type, "Response Content-Type is not application/json"
    data = response.json()
    assert isinstance(data, dict), "Response JSON should be a dictionary"
    product_list = data.get("products") or data.get("data")
    assert isinstance(product_list, list), "Filtered products should be a list"
    for product in product_list:
        brand = product.get("brand") or product.get("brand_name")
        if brand is not None:
            assert "brand-x" in brand.lower(), f"Product brand {brand} does not match filter 'brand-x'"
        price = product.get("price")
        if price is not None:
            assert 100 <= price <= 500, f"Product price {price} outside expected range 100-500"

    # Test sort by price ascending
    params = {
        "sort": "price_asc"
    }
    response = requests.get(f"{BASE_URL}/shop", headers=HEADERS, params=params, timeout=TIMEOUT)
    assert response.status_code == 200, f"Expected 200 but got {response.status_code} for sorting by price_asc"
    content_type = response.headers.get('Content-Type', '').lower()
    assert 'application/json' in content_type, "Response Content-Type is not application/json"
    data = response.json()
    product_list = data.get("products") or data.get("data")
    assert isinstance(product_list, list), "Sorted products should be a list"
    prices = [p.get("price") for p in product_list if p.get("price") is not None]
    assert prices == sorted(prices), "Products not sorted by ascending price"


test_search_and_filter_products()
