(function () {
    "use strict";

    const charts = {}; // 🧠 Guardará todos los gráficos por ID

    // 🎨 Colores
    const chartColors = () => [
        getColor("primary", 0.9),
        getColor("warning", 0.9),
        getColor("pending", 0.9),
    ];

    // 🧩 Función para crear un gráfico
    function createPieChart(chartId, dataValues) {
        const ctx = document.getElementById(chartId);
        if (!ctx) return;

        const chart = new Chart(ctx.getContext("2d"), {
            type: "pie",
            data: {
                labels: ["Entradas registradas", "Entradas NO ingresadas", "Entradas Disponibles"],
                datasets: [
                    {
                        data: dataValues,
                        backgroundColor: chartColors(),
                        hoverBackgroundColor: chartColors(),
                        borderWidth: 5,
                        borderColor: $("html").hasClass("dark") ? getColor("darkmode.700") : getColor("white"),
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
            },
        });

        charts[chartId] = chart;
    }

    // 🧠 Crear gráfico principal
    if ($("#report-pie-chart").length) {
        createPieChart("report-pie-chart", [
            chartData.soldTickets,
            chartData.ticketsNoEntered,
            chartData.availableTickets,
        ]);
    }

    // 🧠 Crear gráficos por tipo de ticket
    ticketData.forEach(ticketInfo => {
        const id = `report-pie-chart-${ticketInfo.ticket_type_id}`;
        if ($(`#${id}`).length) {
            createPieChart(id, [
                ticketInfo.soldTickets,
                ticketInfo.ticketTypeNoEntered,
                ticketInfo.availableTickets,
            ]);
        }
    });

    // 🔄 Actualizar cuando cambie el modo oscuro
    helper.watchClassNameChanges($("html")[0], () => {
        Object.values(charts).forEach(chart => chart.update());
    });

    // ✅ Escuchar los cambios en los checkboxes
    document.querySelectorAll(".legend-controls input.chart-toggle").forEach(input => {
        input.addEventListener("change", e => {
            const chartContainer = e.target.closest(".legend-controls");
            const chartId = chartContainer.dataset.chartId;
            const chart = charts[chartId];
            const index = parseInt(e.target.dataset.index);

            if (!chart) return;

            // Si se desactiva → guardar valor original y poner null
            if (!e.target.checked) {
                chart.data.datasets[0].data[index] = null;
            } else {
                // Restaurar valor original del dataset
                const originalData = getOriginalData(chartId, index);
                chart.data.datasets[0].data[index] = originalData;
            }

            chart.update();
        });
    });

    // 🧩 Función auxiliar para obtener valores originales
    function getOriginalData(chartId, index) {
        if (chartId === "report-pie-chart") {
            return [
                chartData.soldTickets,
                chartData.ticketsNoEntered,
                chartData.availableTickets,
            ][index];
        }

        const ticket = ticketData.find(t => `report-pie-chart-${t.ticket_type_id}` === chartId);
        if (!ticket) return 0;

        return [
            ticket.soldTickets,
            ticket.ticketTypeNoEntered,
            ticket.availableTickets,
        ][index];
    }
})();
