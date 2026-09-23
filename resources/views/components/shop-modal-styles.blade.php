{{--
  Shop Modal Styles — shared CSS for sort/filter modals.
  Usage: <x-shop-modal-styles />
--}}

<style>
  /* Modal styling */
  .custom-modal .modal-content {
    border-radius: 20px 20px 0 0;
    border: none;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
  }
  .custom-modal.modal.fade .modal-dialog {
    transform: translate(0, 100%);
    transition: transform 0.3s ease-out;
  }
  .custom-modal.modal.show .modal-dialog {
    transform: translate(0, 0);
  }
  .custom-modal .modal-dialog {
    margin: 0;
    position: absolute;
    bottom: 0;
    width: 100%;
    max-width: 100%;
  }
  
  .modal-title {
    font-weight: bold;
    font-size: 1.1rem;
    text-align: center;
    width: 100%;
  }
  
  /* Radio styling for sort */
  .custom-radio {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background 0.15s;
  }
  .custom-radio:hover {
    background: #fefce8;
    border-radius: 8px;
    padding-left: 8px;
    padding-right: 8px;
  }
  .custom-radio input[type="radio"] {
    width: 20px;
    height: 20px;
    accent-color: #d4a853;
  }
  
  .sort-directions {
    display: flex;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 20px;
    padding: 4px;
  }
  .sort-dir-btn {
    flex: 1;
    text-align: center;
    padding: 10px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: #64748b;
    font-weight: 500;
    transition: all 0.3s;
    cursor: pointer;
  }
  .sort-dir-btn.active {
    background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(212,168,83,0.3);
  }
  
  /* Sort/Filter container */
  .sort-filter-container {
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #726b80;
    border-radius: 10px;
    padding: 2px 8px;
    margin-right: auto;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }
  
  .sort-filter-btn {
    background: transparent;
    border: none;
    padding: 6px 10px;
    color: #4b5563;
    font-size: 0.95rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: color 0.2s;
    cursor: pointer;
  }
  .sort-filter-btn:hover { color: #d4a853; }
  .sort-filter-btn i { color: #6b7280; font-size: 0.95rem; }
  
  .action-divider {
    color: #1f2937;
    font-weight: bold;
    font-size: 1rem;
    margin: 0 2px;
  }
  
  /* Size badges */
  .size-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    background: #f8f9fa;
    color: #374151;
    border: 1.5px solid #e2e8f0;
    transition: all 0.2s;
  }
  .size-badge.active {
    background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
    color: white;
    border-color: #d4a853;
    box-shadow: 0 2px 8px rgba(212,168,83,0.3);
  }
  .size-badge:hover {
    border-color: #d4a853;
  }
  
  /* Color swatch */
  .color-swatch:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
  }
  
  /* Dual Range Slider */
  .dual-range-wrapper { position: relative; height: 40px; }
  .dual-range-track {
    position: absolute;
    top: 50%; left: 0; right: 0;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    transform: translateY(-50%);
    z-index: 1;
  }
  .dual-range {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 6px;
    background: none;
    position: absolute;
    top: 50%; left: 0;
    transform: translateY(-50%);
    pointer-events: none;
    z-index: 2;
    margin: 0; padding: 0; outline: none;
  }
  .dual-range::-webkit-slider-runnable-track { height: 6px; background: transparent; border: none; }
  .dual-range::-moz-range-track { height: 6px; background: transparent; border: none; }
  .dual-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 24px; height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    cursor: pointer;
    pointer-events: all;
    margin-top: -9px;
  }
  .dual-range::-moz-range-thumb {
    width: 24px; height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    cursor: pointer;
    pointer-events: all;
  }
  .dual-range-min { z-index: 3; }
  .dual-range-max { z-index: 4; }
  
  /* Fix RTL brand select dropdown icon */
  [dir="rtl"] .form-select, .form-select { background-position: left 0.75rem center; }
  
  /* Filled heart wishlist */
  .filled-heart { color: #d4a853 !important; }
</style>
