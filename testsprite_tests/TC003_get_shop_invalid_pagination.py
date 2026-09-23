import requests

def test_get_shop_invalid_pagination():
    base_url = "http://localhost:8000"
    url = f"{base_url}/shop"
    params = {"per_page": 0}
    headers = {"Accept": "application/json"}
    timeout = 30

    try:
        response = requests.get(url, params=params, headers=headers, timeout=timeout)
    except requests.RequestException as e:
        assert False, f"Request failed: {e}"

    # Expecting 400 Bad Request or a validation error status (usually 400 or 422)
    assert response.status_code in (400, 422), f"Expected status 400 or 422, got {response.status_code}"

    # Optionally validate error response structure if JSON returned
    try:
        json_resp = response.json()
        assert "error" in json_resp or "message" in json_resp or "errors" in json_resp, "Error response missing expected fields"
    except ValueError:
        # Response body is not JSON
        pass

test_get_shop_invalid_pagination()