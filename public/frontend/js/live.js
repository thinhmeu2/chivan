function addRandom(tdSl) {

    if (!tdSl) return;

    const columnIndex = tdSl.cellIndex;
    const table = tdSl.closest('table');

    const rows = Array.from(table.querySelectorAll('tbody tr'));

    let orderedRows = [];

    if (tdSl.dataset.code === 'XSMB') {

        orderedRows = [
            rows[2], // g1
            rows[3], // g2
            rows[4], // g3
            rows[5], // g4
            rows[6], // g5
            rows[7], // g6
            rows[8], // g7
            rows[1], // đb
            rows[0], // mã đb
        ].filter(Boolean);

    } else {

        const startRow = tdSl.parentElement;
        const startIndex = rows.indexOf(startRow);
        orderedRows = rows.slice(startIndex);
    }

    for (const row of orderedRows) {

        const td = row.cells[columnIndex];
        if (!td) continue;

        const emptyNumber = td.querySelector('.number:empty');

        if (emptyNumber) {
            emptyNumber.classList.add('random');
            break;
        }
    }
}
document.querySelectorAll('td[data-code]').forEach(tdSl => {
    addRandom(tdSl);
})

function showLoto(tdSl, data) {
    const component = tdSl.closest('.component-kqxs');
    if (!component) return;

    const lotoTable = component.querySelector('table.table-loto');
    if (!lotoTable) return;

    const columnIndex = tdSl.cellIndex;

    const lotoData = buildLoto(data);
    const tds = Array.from(lotoTable.querySelectorAll(`tr:nth-child(n+2) td:nth-child(${columnIndex + 1})`));
    tds.forEach((td, digit) => {
        td.textContent = lotoData[digit].join(', ');
    })
}
function buildLoto(data) {

    const result = Array.from({ length: 10 }, () => []);

    data.forEach(number => {

        if (!number) return;

        const lastTwo = number.slice(-2);
        const head = parseInt(lastTwo[0], 10);

        if (!isNaN(head)) {
            result[head].push(lastTwo);
        }
    });

    return result;
}
Common.loadJs('socket.io.min.js').then(()=>{
    Common.loadCss('random.css');
    const socket = io("https://ketquagiaidacbiet.com", {
        path: "/socket.io/"
    });

    socket.on("data", (data) => {
        data.forEach(item => {
            const tdSl = document.querySelector(`td[data-code="${item.code}"]`);
            if (!tdSl) return;

            const columnIndex = tdSl.cellIndex; // vị trí cột

            const startRow = tdSl.parentElement; // tr chứa tdSl
            const table = startRow.closest('table');
            const allRows = Array.from(table.querySelectorAll('tr'));

            const startIndex = allRows.indexOf(startRow);

            // chỉ lấy từ hàng chứa tdSl trở xuống
            const targetRows = allRows.slice(startIndex);

            const columnTds = targetRows
                .map(row => row.cells[columnIndex])
                .filter(Boolean);

            item.results.forEach((rowNumbers, rowIndex) => {

                const td = columnTds[rowIndex];
                if (!td) return;

                const numberElements = td.querySelectorAll('.number');

                rowNumbers.forEach((num, i) => {
                    if (numberElements[i]) {
                        numberElements[i].textContent = num;
                    }
                });
            });

            // thêm hiệu ứng random sau khi chèn số
            addRandom(tdSl);
            showLoto(tdSl, item.results.flat());
        });
    });
})

