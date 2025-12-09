document.addEventListener("DOMContentLoaded", function () {
    const reviewLink = document.querySelector(".card-info__reviews");
    const tabButtons = document.querySelectorAll(".js-about-tab");
    const tabContents = document.querySelectorAll(".js-tab-content");

    if (reviewLink) {
        reviewLink.addEventListener("click", function (e) {
            e.preventDefault();

            // Убираем active у всех табов
            tabButtons.forEach(btn => btn.classList.remove("is-active"));
            tabContents.forEach(content => content.classList.remove("is-active"));

            // Активируем кнопку "Отзывы"
            const reviewsBtn = document.querySelector('.js-about-tab[data-tab="reviews"]');
            const reviewsContent = document.querySelector('.js-tab-content[data-tab="reviews"]');

            if (reviewsBtn) reviewsBtn.classList.add("is-active");
            if (reviewsContent) reviewsContent.classList.add("is-active");

            // Скроллим к блоку с отзывами
            if (reviewsContent) {
                reviewsContent.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    }
});
