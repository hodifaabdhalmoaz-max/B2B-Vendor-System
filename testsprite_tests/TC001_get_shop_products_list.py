import requests

def test_get_shop_products_list():
    base_url = "http://localhost:8000"
    url = f"{base_url}/shop"
    headers = {
        "Accept": "application/json"
    }
    try:
        response = requests.get(url, headers=headers, timeout=30)
    except requests.RequestException as e:
        assert False, f"Request to GET /shop failed: {e}"

    assert response.status_code == 200, f"Expected status code 200 but got {response.status_code}"

    json_data = None
    try:
        json_data = response.json()
    except ValueError:
        assert False, "Response is not valid JSON"

    # Validate json_data contains paged list structure: assume keys like 'data' for products list, 'current_page', 'last_page' etc.
    # Since exact schema not provided, check minimal
    assert isinstance(json_data, dict), "Response JSON is not a dictionary"
    assert "data" in json_data, "Response JSON does not contain 'data' key for products list"
    assert isinstance(json_data["data"], list), "'data' key is not a list of products"
    # Check pagination-related keys presence or type if available
    # Just basic checks due to lack of exact schema
    for key in ["current_page", "last_page", "per_page", "total"]:
        if key in json_data:
            assert isinstance(json_data[key], (int, type(None))), f"'{key}' key is not int or None"

test_get_shop_products_list()