// products.js

document.addEventListener("DOMContentLoaded", () => {
    let selectedCategory = "all";
    let selectedPriceRanges = [];

    const categoriaButtons = document.querySelectorAll(".categoria-item");
    const priceCheckboxes = document.querySelectorAll(".price-filter-checkbox");
    const gridProductos = document.querySelector(".grid-productos");
    const paginacionDiv = document.querySelector(".paginacion");

    async function fetchFilteredProducts(page = 1) {
        // Usa la variable global
        const url = new URL(window.routes.productosFiltrar); // ¡Aquí está el cambio!
        url.searchParams.append("page", page);

        if (selectedCategory && selectedCategory !== "all") {
            url.searchParams.append("categoria_id", selectedCategory);
        }

        selectedPriceRanges.forEach((range) => {
            url.searchParams.append("min_price[]", range.min);
            url.searchParams.append("max_price[]", range.max);
        });

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    Accept: "application/json",
                },
            });

            if (!response.ok) {
                throw new Error("Error al cargar productos filtrados.");
            }

            const data = await response.json();
            updateProductsUI(data.productos);
        } catch (error) {
            console.error("Error al filtrar productos:", error);
            if (gridProductos)
                gridProductos.innerHTML =
                    '<p class="no-results">Error al cargar productos.</p>';
            if (paginacionDiv) paginacionDiv.innerHTML = "";
        }
    }

    function updateProductsUI(paginatedProducts) {
        if (!gridProductos || !paginacionDiv) return; // Ensure elements exist

        let productsHtml = "";
        if (paginatedProducts.data.length === 0) {
            productsHtml =
                '<p class="no-results">No se encontraron productos con estos filtros.</p>';
        } else {
            productsHtml = paginatedProducts.data
                .map(
                    (producto) => `
            <div class="producto">
                <img src="${producto.imagen_url}" alt="${producto.nombre}" />
                <h4>${producto.nombre}</h4>
                <p>Al precio de:</p>
                <strong class="precio">S/${parseFloat(producto.precio)
                    .toFixed(2)
                    .replace(".", ",")}</strong>
                <button class="btn-agregar" data-producto-id="${
                    producto.id
                }">Agregar</button>
            </div>
            `
                )
                .join("");
        }
        gridProductos.innerHTML = productsHtml;

        let paginationHtml = "";
        if (paginatedProducts.last_page > 1) {
            paginationHtml += `
            <nav class="pagination-saya-container" aria-label="Paginación de productos">
                <ul class="pagination-saya-list">
            `;

            const prevPageClass =
                paginatedProducts.current_page === 1 ? "disabled" : "";
            const prevPageLink =
                paginatedProducts.current_page === 1
                    ? `<span class="pagination-saya-link pagination-prev-next"><i class="fas fa-chevron-left"></i> Anterior</span>`
                    : `<a class="pagination-saya-link pagination-prev-next" href="#" data-page="${
                          paginatedProducts.current_page - 1
                      }" rel="prev"><i class="fas fa-chevron-left"></i> Anterior</a>`;
            paginationHtml += `<li class="pagination-saya-item ${prevPageClass}" aria-disabled="${
                prevPageClass === "disabled"
            }">${prevPageLink}</li>`;

            const currentPage = paginatedProducts.current_page;
            const lastPage = paginatedProducts.last_page;
            const pageRange = 2;

            let pagesToShow = [];
            pagesToShow.push(1);
            for (
                let i = Math.max(2, currentPage - pageRange);
                i <= Math.min(lastPage - 1, currentPage + pageRange);
                i++
            ) {
                pagesToShow.push(i);
            }
            if (lastPage > 1) {
                pagesToShow.push(lastPage);
            }
            pagesToShow = [...new Set(pagesToShow)].sort((a, b) => a - b);

            let lastPrintedPage = 0;
            pagesToShow.forEach((page) => {
                if (page - lastPrintedPage > 1) {
                    paginationHtml += `
                    <li class="pagination-saya-item disabled" aria-disabled="true">
                        <span class="pagination-saya-link ellipsis">...</span>
                    </li>
                    `;
                }

                const isActive = page === currentPage ? "active" : "";
                const pageLinkContent =
                    page === currentPage
                        ? `<span class="pagination-saya-link current-page">${page}</span>`
                        : `<a class="pagination-saya-link" href="#" data-page="${page}">${page}</a>`;
                paginationHtml += `<li class="pagination-saya-item ${isActive}" aria-current="${
                    isActive === "active" ? "page" : "false"
                }">${pageLinkContent}</li>`;

                lastPrintedPage = page;
            });

            const nextPageClass =
                paginatedProducts.current_page === paginatedProducts.last_page
                    ? "disabled"
                    : "";
            const nextPageLink =
                paginatedProducts.current_page === paginatedProducts.last_page
                    ? `<span class="pagination-saya-link pagination-prev-next">Siguiente <i class="fas fa-chevron-right"></i></span>`
                    : `<a class="pagination-saya-link pagination-prev-next" href="#" data-page="${
                          paginatedProducts.current_page + 1
                      }" rel="next">Siguiente <i class="fas fa-chevron-right"></i></a>`;
            paginationHtml += `<li class="pagination-saya-item ${nextPageClass}" aria-disabled="${
                nextPageClass === "disabled"
            }">${nextPageLink}</li>`;

            paginationHtml += `
                </ul>
                <p class="pagination-saya-info">Mostrando ${paginatedProducts.from} al ${paginatedProducts.to} de ${paginatedProducts.total} resultados</p>
            </nav>
            `;
        }
        paginacionDiv.innerHTML = paginationHtml;
    }

    categoriaButtons.forEach((button) => {
        button.addEventListener("click", function () {
            categoriaButtons.forEach((btn) => btn.classList.remove("active"));
            this.classList.add("active");
            selectedCategory = this.dataset.categoriaId || "all";

            priceCheckboxes.forEach((checkbox) => (checkbox.checked = false));
            selectedPriceRanges = [];

            fetchFilteredProducts();
        });
    });

    priceCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", function () {
            const min = parseFloat(this.dataset.minPrice);
            const max = parseFloat(this.dataset.maxPrice);

            if (this.checked) {
                selectedPriceRanges.push({
                    min: min,
                    max: max,
                });
            } else {
                selectedPriceRanges = selectedPriceRanges.filter(
                    (range) => !(range.min === min && range.max === max)
                );
            }
            fetchFilteredProducts();
        });
    });

    if (paginacionDiv) {
        paginacionDiv.addEventListener("click", function (event) {
            const targetLink = event.target.closest("a.pagination-saya-link");
            if (
                targetLink &&
                !targetLink
                    .closest(".pagination-saya-item")
                    .classList.contains("disabled")
            ) {
                event.preventDefault();
                const page = targetLink.dataset.page;
                fetchFilteredProducts(page);
            }
        });
    }

    // Event listener for adding products to cart from the main product grid
    if (gridProductos) {
        gridProductos.addEventListener("click", async function (event) {
            const target = event.target;

            if (target.classList.contains("btn-agregar")) {
                const productId = target.dataset.productoId;
                if (!productId) return;

                try {
                    const response = await fetch("/carrito/añadir", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                        body: JSON.stringify({
                            producto_id: productId,
                            cantidad: 1,
                        }),
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(
                            errorData.message ||
                                "Error al añadir el producto al carrito"
                        );
                    }

                    const data = await response.json();
                    if (data.success) {
                        // Assuming window.updateCartUI is available globally from header.js
                        if (typeof window.updateCartUI === "function") {
                            window.updateCartUI(data);
                        }
                        Swal.fire(
                            "Añadido!",
                            "Producto añadido al carrito.",
                            "success"
                        );
                    } else {
                        Swal.fire(
                            "Error",
                            data.message ||
                                "No se pudo añadir el producto al carrito.",
                            "error"
                        );
                    }
                } catch (error) {
                    console.error("Error al añadir al carrito:", error);
                    Swal.fire(
                        "Error",
                        "Hubo un problema al añadir el producto: " +
                            error.message,
                        "error"
                    );
                }
            }
        });
    }

    // Initial load of products (simulating a click on "Ver todo")
    document.querySelector('.categoria-item[data-categoria-id="all"]')?.click();
});
