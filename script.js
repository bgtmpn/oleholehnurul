let products = [];

async function fetchProducts() {
  try {
    const response = await fetch("get_produk.php");
    products = await response.json();
    renderProducts(products);
  } catch (error) {
    console.error("Gagal mengambil data produk:", error);
  }
}

function renderProducts(items) {
  const grid = document.getElementById("productGrid");
  const emptyState = document.getElementById("emptyState");

  if (!grid) return;

  if (items.length === 0) {
    grid.innerHTML = "";
    if (emptyState) emptyState.classList.remove("hidden");
    return;
  }

  if (emptyState) emptyState.classList.add("hidden");
  grid.innerHTML = items
    .map(
      (product) => `
        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs hover:shadow-md transition-all duration-300 flex flex-col group">
            <!-- Foto Produk -->
            <div class="relative overflow-hidden bg-slate-100 aspect-square">
                <img src="${product.image}" 
                     alt="${product.name}" 
                     loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                     onerror="this.src='https://via.placeholder.com/300?text=Foto+Tidak+Ada'">
            </div>

            <!-- Nama Produk -->
            <div class="p-4 flex-1 flex items-center justify-center">
                <h3 class="font-semibold text-slate-900 text-sm sm:text-base text-center line-clamp-2 group-hover:text-primary-600 transition-colors">
                    ${product.name}
                </h3>
            </div>
        </div>
      `,
    )
    .join("");
}

window.onload = function () {
  fetchProducts();
};
