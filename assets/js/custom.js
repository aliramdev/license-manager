document.addEventListener("DOMContentLoaded", function () {

    // کپی به کلیپ‌بورد
    const copyButtons = document.querySelectorAll(".lm-copy-btn");
    copyButtons.forEach(button => {
        button.addEventListener("click", function () {
            const targetInput = document.querySelector(this.dataset.target);
            if (targetInput) {
                targetInput.select();
                document.execCommand("copy");
                this.innerHTML = '<i class="fas fa-check"></i> کپی شد!';
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-copy"></i> کپی';
                }, 2000);
            }
        });
    });

    // تولید رمز قوی (API Key یا Secret)
    const generateButtons = document.querySelectorAll(".lm-generate-btn");
    generateButtons.forEach(button => {
        button.addEventListener("click", function () {
            const targetInput = document.querySelector(this.dataset.target);
            if (targetInput) {
                const newKey = generateSecureToken();
                targetInput.value = newKey;
            }
        });
    });

    // تابع تولید رمز قوی
    function generateSecureToken(length = 32) {
        const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+";
        let token = "";
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            token += charset[randomIndex];
        }
        return token;
    }

    // تأیید حذف
    const deleteForms = document.querySelectorAll(".lm-delete-form");
    deleteForms.forEach(form => {
        form.addEventListener("submit", function (e) {
            if (!confirm("آیا مطمئن هستید که می‌خواهید این مورد را حذف یا باطل کنید؟")) {
                e.preventDefault();
            }
        });
    });

});
