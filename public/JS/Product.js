// SẢN PHẨM BÁN CHẠY
function showTab(category, event) {
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    if (event && event.target) event.target.classList.add('active');

    document.querySelectorAll('.slider').forEach(slider => slider.classList.remove('active'));
    const activeSlider = document.querySelector(`.slider[data-tab="${category}"]`);
    if (activeSlider) activeSlider.classList.add('active');
}

// Phân loại theo giới tính
function getProductsByGender(data) {
    const categories = { nam: [], nu: [], unisex: [] };
    data.forEach(brand => {
        if (brand.products && Array.isArray(brand.products)) {
            brand.products.forEach(product => {
                let category = 'nu';
                if (product.gender === 'Nam') category = 'nam';
                else if (product.gender === 'Unisex') category = 'unisex';

                categories[category].push({
                    brand: product.brand || brand.name,
                    name: product.name,
                    price: product.price,
                    image: product.image
                });
            });
        }
    });
    return categories;
}

// Định dạng hiển thị giá
function formatPrice(priceStr) {
    if (priceStr && priceStr.includes(' ')) {
        const parts = priceStr.split(' ');
        const oldPrice = parts[0].trim();
        const newPrice = parts.slice(1).join(' ').trim();
        return `
            <div class="price-container">
                <span class="old-price">${oldPrice}</span>
                <span class="new-price">${newPrice}</span>
            </div>
        `;
    }
    return `<strong>${priceStr}</strong>`;
}

// Tạo slider riêng cho từng giới tính
function generateSliderHtml(products, tabId) {
    let randomProducts = products;
    if (products.length > 8) {
        randomProducts = [...products].sort(() => Math.random() - 0.5).slice(0, 8);
    }
    
    let html = `<div class="slider ${tabId === 'nam' ? 'active' : ''}" data-tab="${tabId}">`;
    if (randomProducts.length === 0) {
        html += '<div class="product" style="width:100%;"><p>Không có sản phẩm</p></div>';
    } else {
        randomProducts.forEach(product => {
            html += `
                <div class="product">
                    <img src="${product.image}" alt="${product.name}" 
                         onerror="this.src='https://via.placeholder.com/150?text=No+Image';">
                    <h3>${product.brand}</h3>
                    <p>${product.name}</p>
                    ${formatPrice(product.price)}
                </div>
            `;
        });
    }
    html += '</div>';
    return html;
}

// Render 3 slider riêng biệt
function renderSliders(data) {
    const categories = getProductsByGender(data);
    const namHtml = generateSliderHtml(categories.nam, 'nam');
    const nuHtml = generateSliderHtml(categories.nu, 'nu');
    const unisexHtml = generateSliderHtml(categories.unisex, 'unisex');

    const container = document.querySelector('.slider-container');
    if (container) container.innerHTML = namHtml + nuHtml + unisexHtml;

    addDragScrollToSlider();
}

// Cho phép kéo trượt slider
function addDragScrollToSlider() {
    let currentSlider = null;
    let isDragging = false;
    let startX, scrollLeft;

    function initDrag(element) {
        element.addEventListener('mousedown', (e) => {
            isDragging = true;
            currentSlider = element;
            startX = e.pageX - element.offsetLeft;
            scrollLeft = element.scrollLeft;
            element.style.cursor = 'grabbing';
        });

        element.addEventListener('mouseleave', () => isDragging = false);
        element.addEventListener('mouseup', () => {
            isDragging = false;
            if (currentSlider) currentSlider.style.cursor = 'grab';
        });

        element.addEventListener('mousemove', (e) => {
            if (!isDragging || currentSlider !== element) return;
            e.preventDefault();
            const x = e.pageX - element.offsetLeft;
            const walk = (x - startX) * 2;
            element.scrollLeft = scrollLeft - walk;
        });
    }

    document.querySelectorAll('.slider').forEach(slider => {
        initDrag(slider);
        slider.style.cursor = 'grab';
    });
}

// Gọi API sản phẩm
document.addEventListener('DOMContentLoaded', function() {
    fetch('/san-pham-json')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.error) console.error('Lỗi data:', data.error);
            else renderSliders(data);
        })
        .catch(error => console.error('Fetch error:', error));
});

