document.addEventListener('DOMContentLoaded', () => {
    const btnSpin = document.getElementById('btnSpin');
    if (!btnSpin) return;

    const numbers = Array.from(
        document.querySelectorAll('.table-spin .number')
    );

    if (!numbers.length) return;

    Common.loadCss('random.css');

    const START_DELAY = 1000;
    const SPIN_TIME = 1000;
    const GAP_TIME = 500;

    let index = 0;
    let isRunning = false;

    function resetBoards() {
        // reset bảng kết quả
        document
            .querySelectorAll('.table-spin .number')
            .forEach(el => {
                el.textContent = '';
            });

        // reset bảng loto: bỏ từ cột 2 trở đi
        document
            .querySelectorAll('.table-loto tr')
            .forEach(row => {
                Array.from(row.querySelectorAll('td:nth-child(n+2)'))
                    .forEach(td => td.textContent = '');
            });
    }

    function generateNumber(length) {
        let s = '';
        for (let i = 0; i < length; i++) {
            s += Math.floor(Math.random() * 10);
        }
        return s;
    }

    function insertLoto(el, value) {
        if (!value) return;

        const loto = value.slice(-2);
        const head = parseInt(loto[0], 10);
        if (Number.isNaN(head)) return;

        const td = el.closest('td');
        if (!td) return;

        const tr = td.parentElement;
        if (!tr) return;

        // vị trí cột của number
        const colIndex = Array.from(tr.children).indexOf(td);
        if (colIndex < 0) return;

        const lotoTable = document.querySelector('.table-loto');
        if (!lotoTable) return;

        // đúng hàng theo head
        const targetRow = lotoTable.querySelector(
            `tr:nth-child(${head + 2})`
        );
        if (!targetRow) return;

        const cell = targetRow.children[colIndex];
        if (!cell) return;

        // append, không ghi đè
        const span = document.createElement('span');
        span.textContent = loto + ' ';
        cell.appendChild(span);
    }

    function spinNext() {
        if (index >= numbers.length) {
            isRunning = false;

            btnSpin.disabled = false;
            btnSpin.textContent = 'Quay thử';

            return;
        }

        const el = numbers[index];
        const length = parseInt(el.dataset.l ?? 0, 10);

        el.textContent = '';
        el.classList.add('random');

        setTimeout(() => {
            el.classList.remove('random');

            if (length > 0) {
                const value = generateNumber(length);
                el.textContent = value;
                insertLoto(el, value);
            }

            setTimeout(() => {
                index++;
                spinNext();
            }, GAP_TIME);

        }, SPIN_TIME);
    }

    btnSpin.addEventListener('click', () => {
        if (isRunning) return;

        isRunning = true;
        index = 0;

        // đổi trạng thái nút
        btnSpin.textContent = 'Đang quay thử';
        btnSpin.disabled = true;

        // reset dữ liệu cũ
        resetBoards();

        setTimeout(spinNext, START_DELAY);
    });

});
