import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_search_empty_and_long_query_validation():
    # Test empty search query: expect either 200 or 400
    try:
        response_empty = requests.get(f"{BASE_URL}/shop", params={"search": ""}, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Request to /shop with empty search query failed: {e}"
    assert response_empty.status_code in (200, 400), f"Expected status 200 or 400 for empty search query, got {response_empty.status_code}"
    if response_empty.status_code == 200:
        # Validate response is a list or contains products list (assumed JSON with 'data' or list)
        if not response_empty.content or response_empty.content.strip() in (b'', b'null'):
            assert False, "Response body is empty or null when status is 200 for empty search query"
        try:
            data = response_empty.json()
            assert isinstance(data, dict) or isinstance(data, list), "Response JSON should be dict or list"
            # If dict, expecting products inside (likely in some key). Just check presence of keys.
        except Exception as e:
            assert False, f"Failed to parse JSON from response with status 200 for empty search query: {e}"
    elif response_empty.status_code == 400:
        # Validate error message presence (best effort)
        try:
            error_data = response_empty.json()
            assert "error" in error_data or "message" in error_data or "errors" in error_data, "Error details missing in 400 response for empty search query"
        except Exception as e:
            # JSON parse failure should fail the test
            assert False, f"Failed to parse JSON from 400 response for empty search query: {e}"

    # Test very long search query: expect 422 Validation Error
    very_long_query = "a" * 5000
    try:
        response_long = requests.get(f"{BASE_URL}/shop", params={"search": very_long_query}, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Request to /shop with very long search query failed: {e}"
    assert response_long.status_code == 422, f"Expected status 422 for very long search query, got {response_long.status_code}"
    try:
        error_data = response_long.json()
        # Check validation error keys presence (generic)
        assert any(key in error_data for key in ("errors", "message", "error")), "Validation error details missing in 422 response for long search query"
    except Exception as e:
        assert False, f"Failed to parse JSON from 422 response for long search query: {e}"

test_search_empty_and_long_query_validation()
