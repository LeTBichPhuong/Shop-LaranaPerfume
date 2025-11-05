document.addEventListener("DOMContentLoaded", async () => {
  const filter = document.getElementById("brands-filter");
  const list = document.getElementById("brand-list");
  const brandGrid = document.querySelector('.brand-grid');

  // Tạo bộ lọc A–Z
  const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
  letters.forEach(l => {
    const li = document.createElement("li");
    li.innerHTML = `<a data-letter="${l}" href="#">${l}</a>`;
    filter.appendChild(li);
  });

  // Fetch dữ liệu brand từ API
  let brands = [];
  try {
    const res = await fetch("/thuong-hieu-json");
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    brands = await res.json();
  } catch (e) {
    console.error("Không thể tải dữ liệu thương hiệu:", e);
    return;
  }

  // Render grid 12 brands ngẫu nhiên
  if (brandGrid) {
    // Lọc chỉ thương hiệu có logo
    const brandsWithLogo = brands.filter(brand => brand.logo && brand.logo.trim() !== '');

    // Hàm shuffle mảng để lấy ngẫu nhiên
    function shuffle(array) {
      return array.slice().sort(() => Math.random() - 0.5);
    }

    // Lấy 12 thương hiệu ngẫu nhiên
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

    brandGrid.innerHTML = html;
  }

  // Gom thương hiệu theo chữ cái đầu
  function groupBrands() {
    const grouped = {};
    brands.forEach(b => {
      const first = b.name[0]?.toUpperCase();
      if (!grouped[first]) grouped[first] = [];
      grouped[first].push(b);
    });
    return grouped;
  }

  const grouped = groupBrands();

  // Render danh sách thương hiệu
  function renderBrands() {
    list.innerHTML = "";
    const container = document.createElement("div");
    container.classList.add("brand-container");

    Object.keys(grouped).sort().forEach(k => {
      const groupDiv = document.createElement("div");
      groupDiv.classList.add("brand-group");
      groupDiv.dataset.letter = k;

      // Render từng thương hiệu với link clickable
      const brandHTML = grouped[k]
        .map(
          b => `
            <li class="brand-item">
              <a href="/thuong-hieu/${encodeURIComponent(b.name)}" style="text-decoration: none; color: inherit; display: flex; align-items: center; width: 100%;">
                <span class="brand-name">${b.name}</span>
                ${
                  b.logo
                    ? `<div class="brand-logo"><img src="${b.logo}" alt="${b.name} logo"></div>`
                    : ""
                }
              </a>
            </li>
          `
        )
        .join("");

      groupDiv.innerHTML = `
        <h3 class="brand-letter">${k}</h3>
        <ul class="brand-list">${brandHTML}</ul>
      `;

      container.appendChild(groupDiv);
    });

    list.appendChild(container);
  }

  renderBrands();

  // Hiệu ứng lọc chữ cái
  function filterBrands(letter) {
    const groups = document.querySelectorAll(".brand-group");
    groups.forEach(group => {
      if (letter === "all" || group.dataset.letter === letter) {
        group.classList.remove("dimmed");
      } else {
        group.classList.add("dimmed");
      }
    });
  }

  // Sự kiện click filter
  filter.addEventListener("click", e => {
    if (e.target.tagName === "A") {
      e.preventDefault();
      document.querySelectorAll("#brands-filter a").forEach(a => a.classList.remove("active"));
      e.target.classList.add("active");
      filterBrands(e.target.dataset.letter);
    }
  });
});