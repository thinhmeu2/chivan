let isMobile = window.outerWidth < 992;
let baseUrl = window.location.origin;

document.addEventListener('DOMContentLoaded', () => {
    Modal.init();

    Common.eventResize(() => {
        Common.redefineVariable();
        Common.responsiveFooter();
    });

    bindLoadMoreButton();
    Common.init();
    Header.init();
});

function bindLoadMoreButton() {
    document.querySelectorAll('.btnLoadMore[data-src]:not([data-bound])').forEach(btn => {
        btn.dataset.bound = '1';

        btn.addEventListener('click', async () => {
            let result = await postData('get', btn.dataset.src);
            let code = result.status;
            result = result.json()

            result.then(rs => {
                if (code === 200){
                    btn.outerHTML = rs.html;
                    bindLoadMoreButton();
                }
            })
        });
    });
}
function showLoading() {
    document.querySelector('body').classList.add('blur');
}
function hideLoading() {
    document.querySelector('body').classList.remove('blur');
}
function validateField(el, message = '') {
    el.setCustomValidity(message);
    el.reportValidity();

    if (message !== '') {
        const handler = () => {
            el.setCustomValidity('');       // bỏ lỗi
            el.removeEventListener('input', handler); // gỡ sự kiện sau khi sửa
        };

        el.addEventListener('input', handler);
    }
}

async function postData(method, url, data = {}, effect = true) {
    method = method.toUpperCase();

    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
    };

    let body = null;

    if (method === 'GET') {
        const params = new URLSearchParams();

        if (data instanceof FormData) {
            for (const [key, value] of data.entries()) {
                params.append(key, value);
            }
        } else {
            for (const key in data) {
                if (data.hasOwnProperty(key)) {
                    params.append(key, data[key]);
                }
            }
        }

        const query = params.toString();
        if (query) {
            url += (url.includes('?') ? '&' : '?') + query;
        }

    } else if (data instanceof FormData) {
        if (method !== 'POST') {
            data.append('_method', method);
            method = 'POST';
        }
        body = data;

    } else {
        if (method !== 'POST') {
            data._method = method;
            method = 'POST';
        }
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(data);
    }

    if (effect)
        showLoading();
    const response = await fetch(url, {
        method,
        headers,
        body,
    });
    if (effect)
        hideLoading();

    return response;
}

class Common {
    static resizeTimeout = null;
    static #loadedFiles = {};
    static async loadJs(src) {
        if (! /^https?:\/\//i.test(src))
            src = `${baseUrl}/frontend/js/${src}?v=${ver}`;

        if (this.#loadedFiles[src]) return this.#loadedFiles[src];

        this.#loadedFiles[src] = new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) return resolve();
            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = resolve;
            script.onerror = () => reject(new Error(`Failed to load JS: ${src}`));
            document.head.appendChild(script);
        });

        return this.#loadedFiles[src];
    }
    static async loadCss(href) {
        if (! /^https?:\/\//i.test(href))
            href = `${baseUrl}/frontend/css/${href}`;

        if (this.#loadedFiles[href]) return this.#loadedFiles[href];

        this.#loadedFiles[href] = new Promise((resolve, reject) => {
            if (document.querySelector(`link[href="${href}"]`)) return resolve();
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.onload = resolve;
            link.onerror = () => reject(new Error(`Failed to load CSS: ${href}`));
            document.head.appendChild(link);
        });

        return this.#loadedFiles[href];
    }
    static async loadSource(jsSrc, cssSrc) {
        const tasks = [];
        if (jsSrc) tasks.push(this.loadJs(jsSrc));
        if (cssSrc) tasks.push(this.loadCss(cssSrc));
        await Promise.all(tasks);
    }

    static eventResize(callback, delay = 500) {
        if (typeof callback !== 'function') return;

        callback();
        window.addEventListener('resize', () => {
            clearTimeout(Common.resizeTimeout);
            Common.resizeTimeout = setTimeout(() => {
                callback();
            }, delay);
        });
    }

    static redefineVariable() {
        isMobile = window.outerWidth < 992;
    }

    static formSubmit() {
        document.addEventListener('submit', async e => {
            const form = e.target;

            if (!(form instanceof HTMLFormElement)) return;
            if (form.hasAttribute('data-skip-ajax')) return;

            e.preventDefault();

            // ✅ beforeSend logic khác
            const beforeSend = form.dataset.beforesend;
            if (typeof beforeSend === 'function') {
                if (beforeSend(form) === false){
                    return
                }
            }

            const onSuccess = form.dataset.onsuccess && window[form.dataset.onsuccess];
            const onError = form.dataset.onerror && window[form.dataset.onerror];

            try {
                let result = await postData(form.method, form.action, new FormData(form));
                let code = result.status;
                result = result.json();

                result.then(async rs => {
                    switch (code){
                        case 200: {
                            if (rs.message) {
                                await Toastr.success(rs.message);
                            }
                            onSuccess?.(rs, form);
                            break;
                        }
                        case 422: {
                            this.renderFormErrors(rs.errors, form);
                            onError?.(rs, form);
                            break;
                        }
                        default: onError?.(rs, form);
                    }
                });
            } catch (err) {
                await Toastr.warning(err);
            }
        });
    }

    static renderFormErrors(errors, form) {
        if (typeof errors !== 'object') return;

        for (const [field, messages] of Object.entries(errors)) {
            let formId = form.id;
            let input = form.querySelector(`[name="${field}"]`);
            if (! input && formId)
                input = document.querySelector(`[name="${field}"][form=${formId}]`)
            if (! input){
                Toastr.error(messages.join('\n'));
            } else {
                validateField(input, messages.join('\n'));
            }
        }
    }

    static initLiveSearch(formSelector = '#formSearch') {
        const form = document.querySelector(formSelector);
        if (!form) return;

        const input = form.querySelector('input[name="s"]');
        const resultBox = form.querySelector('.jsShowResult');
        let debounceTimeout;

        const toggleResultBox = (show) => {
            resultBox.classList.toggle('d-flex', show);
            resultBox.classList.toggle('d-none', !show);
        };

        input.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(async () => {
                const query = input.value.trim();
                if (!query) {
                    resultBox.innerHTML = '';
                    toggleResultBox(false);
                    return;
                }

                const formData = new FormData();
                formData.append('s', query);
                formData.append('liveSearch', '1');

                let result = await postData('get', form.action, formData, false);
                let code = result.status;
                result = result.json();
                result.then(rs => {
                    if (code === 200) {
                        resultBox.innerHTML = rs.html || '';
                        toggleResultBox(true);
                    } else {
                        toggleResultBox(false);
                    }
                })
            }, 300);
        });

        document.addEventListener('pointerdown', (e) => {
            const isInside = form.contains(e.target);
            if (!isInside) {
                toggleResultBox(false);
            } else if (resultBox.innerHTML.trim()) {
                toggleResultBox(true);
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                toggleResultBox(false);
                input.blur();
            }
        });
    }
    static #replaceThumbYoutube(){
        document.querySelectorAll('.changeIframe').forEach(i => {
            i.addEventListener('click', ()=>{
                const boxIframe = i.closest('.box-iframe'); // Tìm phần tử cha gần nhất có class 'box-iframe'
                if (boxIframe) {
                    const iframe = boxIframe.querySelector('iframe'); // Tìm thẻ iframe bên trong .box-iframe
                    if (iframe) {
                        const currentSrc = iframe.getAttribute('src');
                        if (currentSrc && !currentSrc.includes('autoplay=1')) {
                            const newSrc = currentSrc + (currentSrc.includes('?') ? '&' : '?') + 'autoplay=1&mute=1';
                            iframe.setAttribute('src', newSrc);
                        }
                    }
                    i.remove(); // Loại bỏ nút changeIframe sau khi đã xử lý
                }
            })
        })
    }
    static scrollToTop() {

    }
    static lazyImage(){
        document.querySelectorAll('img[data-lazy]').forEach(i => {
            i.src = i.dataset.lazy;
        })
    }
    static responsiveFooter(){
        let details = document.querySelectorAll('footer details');
        if (! isMobile) {
            details.forEach(details => details.open = true);
        } else {
            details.forEach(details => details.open = false);
        }
    }

    static init() {
        this.formSubmit();
        this.initLiveSearch();
        this.#replaceThumbYoutube();
        this.scrollToTop();
        setTimeout(this.lazyImage, 1000);
        this.loadGa();
    }
    static loadGa(){
        if (typeof ga != "undefined" || ! ga) {
            const script = document.createElement('script');
            script.src = `https://www.googletagmanager.com/gtag/js?id=${ga}`;
            document.head.appendChild(script);

            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }

            // Thiết lập trạng thái đồng ý mặc định
            /*gtag('consent', 'default', {
                'ad_storage': 'denied', // Từ chối cookie quảng cáo
                'analytics_storage': 'granted', // Cho phép cookie phân tích (tùy bạn)
                'wait_for_update': 500 // Chờ 500ms để người dùng đồng ý
            });*/

            gtag('js', new Date());
            gtag('config', ga, {
                'allow_ad_personalization_signals': false,
                'send_page_view': false // nếu không muốn tự động gửi page_view
            });

            // Khi người dùng đồng ý:
            // gtag('consent', 'update', {
            //   //'ad_storage': 'granted'
            // });
        }
    }
}
class Toastr {
    static #loadingPromise = null;

    static async #loadToastify() {
        if (this.#loadingPromise) return this.#loadingPromise;

        this.#loadingPromise = new Promise((resolve, reject) => {
            // 1. Load CSS
            const link = document.createElement("link");
            link.rel = "stylesheet";
            link.href = "/frontend/css/toastify.min.css";
            document.head.appendChild(link);

            // 2. Load JS
            const script = document.createElement("script");
            script.src = "/frontend/js/toastify-js.js";
            script.onload = () => {
                resolve();
            };
            script.onerror = reject;
            document.head.appendChild(script);
        });

        return this.#loadingPromise;
    }

    static async success(text) {
        try {
            await this.#loadToastify();
            Toastify({
                text: text,
                backgroundColor: "#03CA0A"
            }).showToast();
        } catch (e){
            console.log(e)
        }

    }

    static async warning(text) {
        await this.#loadToastify();
        Toastify({
            text: text,
            backgroundColor: "#FFAE0D"
        }).showToast();
    }

    static async error(text) {
        await this.#loadToastify();
        Toastify({
            text: text,
            backgroundColor: "#ED1B24"
        }).showToast();
    }
}

class Modal {
    static html = document.getElementsByTagName('html');
    static addEventDefault() {
        document.querySelectorAll('dialog:not([data-inited])').forEach(dialog => {
            dialog.dataset.inited = 1;

            dialog.addEventListener('toggle', () => {
                if (dialog.open) {
                    dialog.dispatchEvent(new Event('modal-opened'));
                } else {
                    dialog.dispatchEvent(new Event('modal-closed'));
                }
            });

            dialog.addEventListener('modal-opened', () => {
                if (dialog.matches(':modal')) {
                    document.documentElement.classList.add('overflow-hidden');
                }
            });

            dialog.addEventListener('modal-closed', () => {
                // kiểm tra xem còn dialog modal nào đang mở không
                const stillModal = document.querySelector('dialog:modal[open]');
                if (!stillModal) {
                    document.documentElement.classList.remove('overflow-hidden');
                }
            });
        });
    }
    static addEventOpen() {
        document.querySelectorAll('[data-target-modal]').forEach(i => {
            i.addEventListener('click', () => {
                let target = i.dataset.targetModal ? document.querySelector(i.dataset.targetModal) : i.closest('dialog');
                if (!target) return;
                if (target.open) {
                    target.close();
                } else {
                    target.showModal();
                }
            })
        })
    }

    static addEventClose() {
        document.querySelectorAll('dialog').forEach(i => {
            /*click backdrop hide modal*/
            i.addEventListener('click', (event) => {
                if (event.target === i) {
                    i.close();
                }
            })
        });
    }

    static init(){
        this.addEventDefault();
        this.addEventOpen();
        this.addEventClose();
    }
}

class Header {
    static initedMenuMobile = false;
    static initMenuMobi() {
        document.querySelector('#btnShowMenuMobile').addEventListener('click', (event)=>{
            let btn = event.target;

            if (! isMobile || this.initedMenuMobile) return;

            this.initedMenuMobile = true;

            // clone menu PC sang mobile
            document.querySelector('#boxMenuMobile').innerHTML =
                document.querySelector('#boxMenuPc').innerHTML;

            // tìm tất cả sub-menu trong menu mobile
            document
                .querySelectorAll('#boxMenuMobile ul.sub-menu')
                .forEach(function (subMenu) {
                    // tạo icon
                    const icon = document.createElement('i');
                    icon.className = 'd-inline-block p-2 icon-arrow-down';

                    // chèn icon trước sub-menu
                    subMenu.parentNode.insertBefore(icon, subMenu);
                });

            document
                .querySelectorAll('#boxMenuMobile .icon-arrow-down')
                .forEach(function (icon) {
                    icon.addEventListener('click', function () {
                        const subMenu = icon.nextElementSibling;
                        console.log(subMenu);
                        subMenu.classList.toggle('d-flex');
                    });
                });
        });
    }
    static init(){
        this.initMenuMobi();
    }
}
