import requests

def test_place_order_unauthenticated():
    base_url = "http://localhost:8000"
    url = f"{base_url}/place-an-order"
    payload = {
        "shipping_address": "123 Test St, Test City",
        "payment_method": "credit_card"
    }
    headers = {
        "Content-Type": "application/json"
    }
    try:
        response = requests.post(url, json=payload, headers=headers, timeout=30)
    except requests.RequestException as e:
        assert False, f"Request failed: {e}"

    assert response.status_code == 401, f"Expected 401 Unauthorized, got {response.status_code}"

test_place_order_unauthenticated()