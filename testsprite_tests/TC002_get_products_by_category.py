import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_get_products_by_category():
    # Known existing category slug for test; if none known, we can first get a category (here assumed 'strollers' from PRD example)
    existing_slug = "strollers"
    non_existent_slug = "non-existent-category"

    # Test success case: existing category
    try:
        url = f"{BASE_URL}/shop/category/{existing_slug}"
        response = requests.get(url, timeout=TIMEOUT)
        assert response.status_code == 200, f"Expected 200, got {response.status_code}"
        data = response.json()
        assert isinstance(data, list) or (isinstance(data, dict) and "data" in data), "Response should be a list or contain 'data' key"
        # Additional check: all products returned should belong to the requested category slug if category info is included
        # But since schema is not explicitly detailed, we only check presence of products
        assert len(data) > 0 or (isinstance(data, dict) and len(data.get("data", [])) >= 0), "Expected products list in response"
    except requests.RequestException as e:
        assert False, f"Request failed: {e}"

    # Test error case: non-existent category
    try:
        url = f"{BASE_URL}/shop/category/{non_existent_slug}"
        response = requests.get(url, timeout=TIMEOUT)
        assert response.status_code == 404, f"Expected 404 for non-existent category, got {response.status_code}"
    except requests.RequestException as e:
        assert False, f"Request failed: {e}"

test_get_products_by_category()