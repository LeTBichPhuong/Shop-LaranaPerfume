// Chức năng tìm kiếm sản phẩm và thương hiệu theo thời gian thực
document.addEventListener('DOMContentLoaded', function () {
    const searchIcon = document.getElementById('searchIcon');
    const searchForm = document.getElementById('searchForm');
    const input = searchForm.querySelector('input[name="keyword"]');
    const resultsBox = document.getElementById('searchResults');

    // Hàm chống spam
    function debounce(fn, wait = 220) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    // Ẩn/hiện ô tìm kiếm khi nhấn vào icon kính lúp
    searchIcon.addEventListener('click', function (e) {
        e.preventDefault();
        if (searchForm.style.display === 'block') {
            searchForm.style.display = 'none';
            resultsBox.style.display = 'none';
        } else {
            searchForm.style.display = 'block';
            input.focus();
        }
    });

    // Ngăn form tự submit khi nhấn Enter
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
    });

    // Ẩn khung kết quả khi click ra ngoài khu vực tìm kiếm
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-container')) {
            searchForm.style.display = 'none';
            resultsBox.style.display = 'none';
        }
    });

    // Gửi yêu cầu đến route để lấy dữ liệu JSON
    async function fetchSuggestions(q) {
        try {
            const res = await fetch(`/ajax/tim-kiem?keyword=${encodeURIComponent(q)}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) {
                console.error('Kết quả trả về không hợp lệ', res.status);
                return null;
            }
            const json = await res.json();
            return json;
        } catch (err) {
            console.error('Lỗi khi gọi API tìm kiếm', err);
            return null;
        }
    }

    // Hiển thị khi không có kết quả
    function renderEmpty() {
        resultsBox.innerHTML = '<p style="padding:12px;color:#666;">Không tìm thấy kết quả</p>';
        resultsBox.style.display = 'block';
    }

    // Hiển thị danh sách kết quả (sản phẩm + thương hiệu)
    function renderResults(data) {
        if (!data) {
            renderEmpty();
            return;
        }
        const products = data.products || [];
        const brands = data.brands || [];

        if (products.length === 0 && brands.length === 0) {
            renderEmpty();
            return;
        }

        let html = '';

        // Hiển thị danh sách sản phẩm
        if (products.length > 0) {
            html += '<h4>Sản phẩm</h4>'; 
            products.forEach(p => {
                const img = p.image ? (p.image.startsWith('http') ? p.image : '/storage/' + p.image) : '/img/placeholder-product.png';
                let price = '';
                if (p.price) {
                    const val = String(p.price).trim();
                    if (val.includes('₫') || val.includes('đ')) {
                        price = val;
                    } else if (!isNaN(val)) {
                        price = Number(val).toLocaleString('vi-VN') + ' ₫';
                    } else {
                        price = val;
                    }
                }
                
                // Dùng slug hoặc ID cho URL an toàn hơn (thay vì encode name)
                const productUrl = `/san-pham/${p.id}-${encodeURIComponent(p.name.replace(/[^a-zA-Z0-9\s]/g, ''))}`;  // Ví dụ slug đơn giản
                
                html += `
                    <a class="search-item" href="${productUrl}">
                        <img src="${img}" alt="${escapeHtml(p.name)}" onerror="this.src='/img/placeholder-product.png'">
                        <div>
                            <div style="font-weight:500;">${escapeHtml(p.name)}</div>
                            <div class="price">${price || 'Liên hệ'}</div>
                        </div>
                    </a>
                `;
            });
        }

        // Hiển thị danh sách thương hiệu
        if (brands.length > 0) {
            html += '<h4>Thương hiệu</h4>';
            brands.forEach(b => {
                const logo = b.logo ? (b.logo.startsWith('http') ? b.logo : '/storage/' + b.logo) : '/img/no-logo.png';
                html += `
                    <a class="search-item" href="/thuong-hieu/${encodeURIComponent(b.name)}">
                        <img src="${logo}" alt="${escapeHtml(b.name)}" onerror="this.src='/img/no-logo.png'">
                        <div style="font-weight:500;">${escapeHtml(b.name)}</div>
                    </a>
                `;
            });
        }

        resultsBox.innerHTML = html;
        resultsBox.style.display = 'block';
    }

    // Hàm chống lỗi 
    function escapeHtml(s) {
        return String(s || '').replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Bắt sự kiện người dùng gõ vào ô tìm kiếm
    const handleInput = debounce(async function () {
        const q = input.value.trim();
        if (q.length < 2) {
            resultsBox.style.display = 'none';
            return;
        }

        // Hiển thị trạng thái đang tải
        resultsBox.innerHTML = '<p style="padding:12px;color:#666;">Đang tìm...</p>';
        resultsBox.style.display = 'block';

        // Gửi yêu cầu tìm kiếm và hiển thị kết quả
        const json = await fetchSuggestions(q);
        renderResults(json);
    }, 250);

    // Gắn sự kiện input vào ô nhập
    input.addEventListener('input', handleInput);
});

// Sản phẩm
document.addEventListener("DOMContentLoaded", function () {
    const navItems = document.querySelectorAll(".nav-item");

    navItems.forEach(navItem => {
        const link = navItem.querySelector("a");
        const submenu = navItem.querySelector(".submenu");

        link.addEventListener("click", function (e) {
            if (submenu && e.target.closest(".submenu-toggle")) {
                e.preventDefault();
                e.stopPropagation();
                navItem.classList.toggle("active");
            } 
        });

        // Nếu muốn submenu mở khi hover
        navItem.addEventListener("mouseenter", () => navItem.classList.add("active"));
        navItem.addEventListener("mouseleave", () => navItem.classList.remove("active"));
    });

    // Ẩn menu khi click ra ngoài
    document.addEventListener("click", function (e) {
        navItems.forEach(navItem => {
            if (!navItem.contains(e.target)) {
                navItem.classList.remove("active");
            }
        });
    });
});


// Tài khoản
document.addEventListener("DOMContentLoaded", () => {
  const links = document.querySelectorAll(".account-sidebar a");
  const sections = document.querySelectorAll(".content-section, .account-section");

  // Ẩn tất cả section
  sections.forEach(s => (s.style.display = "none"));

  // Tìm link có active và mở đúng section
  const activeLink = document.querySelector(".account-sidebar a.active");
  if (activeLink) {
    const section = document.getElementById(activeLink.dataset.section);
    if (section) section.style.display = "block";
  }

  // Khi click vào menu bên trái
  links.forEach(link => {
    link.addEventListener("click", e => {
      e.preventDefault();

      // Xóa trạng thái active cũ
      links.forEach(l => l.classList.remove("active"));
      sections.forEach(s => (s.style.display = "none"));

      // Thêm active cho link mới
      link.classList.add("active");
      const section = document.getElementById(link.dataset.section);
      if (section) section.style.display = "block";
    });
  });
});

function toggleForm(id) {
  const form = document.getElementById(id);
  form.classList.toggle("form-visible");
}

// Thương hiệu nội bật
// Fetch dữ liệu brand từ API 
async function loadBrands() {
  let brands = [];
  try {
    const res = await fetch("/thuong-hieu-json");
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    brands = await res.json();
  } catch (e) {
    console.error("Không thể tải dữ liệu thương hiệu:", e);
    return;
  }

  // Lọc chỉ thương hiệu có logo 
  const brandsWithLogo = brands.filter(brand => brand.logo && brand.logo.trim() !== '');

  // Hàm shuffle mảng để lấy ngẫu nhiên
  function shuffle(array) {
    return array.slice().sort(() => Math.random() - 0.5);
  }

  // Lấy 12 thương hiệu ngẫu nhiên từ những brand có logo 
  const randomBrands = shuffle(brandsWithLogo).slice(0, 12);

  // Tạo HTML động cho brand-grid với link clickable
  let html = '';
  randomBrands.forEach(brand => {
    html += `
      <a href="/thuong-hieu/${encodeURIComponent(brand.name)}" class="brand-link">
        <img src="${brand.logo}" alt="${brand.name}" loading="lazy">
      </a>
    `;
  });

  // Chèn HTML vào DOM
  const brandGrid = document.querySelector('.brand-grid');
  if (brandGrid) {
    brandGrid.innerHTML = html;
  } else {
    console.error("Không tìm thấy phần tử .brand-grid trong DOM");
  }
}

// Chạy khi DOM load xong
document.addEventListener('DOMContentLoaded', loadBrands);

// Quản lý đơn hàng
document.addEventListener('DOMContentLoaded', function() {
    
    // Lấy CSRF token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.content;
        
        const input = document.querySelector('input[name="_token"]');
        if (input) return input.value;
        
        console.error('CSRF token not found!');
        return '';
    }

    // Hủy đơn hàng
    const cancelButtons = document.querySelectorAll('.cancel-order');
    
    cancelButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const orderId = this.getAttribute('data-order-id');
            
            if (!orderId) {
                alert('Không tìm thấy ID đơn hàng!');
                return;
            }

            if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) {
                return;
            }

            // Disable button và show loading
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';

            try {
                const response = await fetch(`/don-hang/cancel/${orderId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                // Log response để debug
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                // Kiểm tra response có phải JSON không
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    throw new Error('Server trả về dữ liệu không hợp lệ. Vui lòng kiểm tra route và controller.');
                }

                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    alert(data.message || 'Đã hủy đơn hàng thành công!');
                    
                    // Reload trang sau 1 giây
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert(data.message || 'Không thể hủy đơn hàng!');
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }

            } catch (error) {
                console.error('Cancel order error:', error);
                alert('Có lỗi xảy ra: ' + error.message);
                
                // Restore button
                this.disabled = false;
                this.innerHTML = originalHTML;
            }
        });
    });

    // Xem chi tiết đơn hàng 
    const viewDetailButtons = document.querySelectorAll('.view-order-detail');
    
    viewDetailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            window.location.href = `/don-hang/${orderId}`;
        });
    });

    // Filter đơn hàng theo trạng thái
    const statusFilter = document.getElementById('order-status-filter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const status = this.value;
            const orderCards = document.querySelectorAll('.order-card');
            
            orderCards.forEach(card => {
                if (status === 'all' || card.getAttribute('data-status') === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});

// chatbot
function toggleChatbot() {
  const box = document.getElementById("chatbot-box");
  if (!box) {
    console.error("Element 'chatbot-box' not found");
    return;
  }
  box.style.display = box.style.display === "block" ? "none" : "block";
}

function appendMessage(message, sender = "bot") {
  const messages = document.getElementById("chatbot-messages");
  if (!messages) {
    console.error("Element 'chatbot-messages' not found");
    return;
  }
  
  const msgDiv = document.createElement("div");
  msgDiv.classList.add("message", sender === "user" ? "user-message" : "bot-message");
  
  const p = document.createElement("p");
  p.textContent = String(message);
  msgDiv.appendChild(p);
  messages.appendChild(msgDiv);
  messages.scrollTop = messages.scrollHeight;
}

async function sendMessage() {
  const input = document.getElementById("chatbot-input");
  if (!input) {
    console.error("Element 'chatbot-input' not found");
    return;
  }
  
  const text = input.value.trim();
  if (!text) return;
  
  // Hiển thị tin nhắn người dùng
  appendMessage(text, "user");
  input.value = "";
  
  // Hiển thị trạng thái đang nhập
  const typingId = `typing-${Date.now()}`;
  const typingDiv = document.createElement("div");
  typingDiv.classList.add("message", "bot-message");
  typingDiv.dataset.typingId = typingId;
  
  const tp = document.createElement("p");
  tp.textContent = "Đang nhập...";
  typingDiv.appendChild(tp);
  
  const messages = document.getElementById("chatbot-messages");
  if (messages) {
    messages.appendChild(typingDiv);
    messages.scrollTop = messages.scrollHeight;
  }
  
  try {
    // Lấy CSRF token
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
      throw new Error("CSRF token not found");
    }
    
    const response = await fetch("/chatbot", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfMeta.content,
        "Accept": "application/json"
      },
      body: JSON.stringify({ message: text })
    });
    
    let reply = "Xin lỗi, hệ thống bận.";
    
    if (response.ok) {
      try {
        const data = await response.json();
        reply = data.reply || data.message || reply;
      } catch (parseError) {
        console.error("Error parsing response:", parseError);
      }
    } else if (response.status === 419) {
      reply = "Phiên làm việc hết hạn. Vui lòng tải lại trang.";
    } else if (response.status === 429) {
      reply = "Bạn đang gửi tin nhắn quá nhanh. Vui lòng đợi 1 phút.";
    } else if (response.status >= 500) {
      reply = "Máy chủ đang gặp sự cố. Thử lại sau.";
    } else {
      try {
        const errorData = await response.json();
        reply = errorData.reply || errorData.message || reply;
      } catch (_) {
        console.error("Error reading error response");
      }
    }
    
    // Xóa trạng thái đang nhập
    const typingEl = document.querySelector(`.bot-message[data-typing-id="${typingId}"]`);
    if (typingEl) typingEl.remove();
    
    // Hiển thị phản hồi
    appendMessage(reply, "bot");
    
  } catch (error) {
    console.error("Chatbot error:", error);
    
    // Xóa trạng thái đang nhập
    const typingEl = document.querySelector(`.bot-message[data-typing-id="${typingId}"]`);
    if (typingEl) typingEl.remove();
    
    appendMessage("Xin lỗi, có lỗi xảy ra khi kết nối!", "bot");
  }
}

function handleKey(event) {
  if (event.key === "Enter") {
    event.preventDefault();
    sendMessage();
  }
}

// Hiển thị tin nhắn chào mừng khi load trang
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(() => {
    appendMessage("Xin chào! Tôi là trợ lý Larana Perfume. Tôi có thể giúp gì cho bạn? ", "bot");
  }, 500);
});