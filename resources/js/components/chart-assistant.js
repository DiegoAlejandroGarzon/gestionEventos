(function () {
    "use strict";

    // console.log(chartData);
    // Gráfico de asistencia general
    if ($("#report-pie-chart").length) {
        const chartColors = () => [
            getColor("primary", 0.9),
            getColor("pending", 0.9),
        ];

        const ctx = $("#report-pie-chart")[0].getContext("2d");
        const reportPieChart = new Chart(ctx, {
            type: "pie",
            data: {
                labels: [
                    "Entradas registradas",
                    "Entradas Disponibles"
                ],
                datasets: [
                    {
                        data: [chartData.soldTickets, chartData.availableTickets],
                        backgroundColor: chartColors(),
                        hoverBackgroundColor: chartColors(),
                        borderWidth: 5,
                        borderColor: () =>
                            $("html").hasClass("dark")
                                ? getColor("darkmode.700")
                                : getColor("white"),
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
            },
        });

        helper.watchClassNameChanges($("html")[0], (currentClassName) => {
            reportPieChart.update();
        });
    }

    // Generar gráficos para cada tipo de ticket
    ticketData.forEach(ticketInfo => {
        const ticketChartId = `report-pie-chart-${ticketInfo.ticket_type_id}`;
        if ($(`#${ticketChartId}`).length) {
            const ticketChartColors = () => [
                getColor("primary", 0.9),
                getColor("pending", 0.9),
            ];

            const ticketCtx = $(`#${ticketChartId}`)[0].getContext("2d");
            new Chart(ticketCtx, {
                type: "pie",
                data: {
                    labels: [
                        "Entradas registradas",
                        "Entradas Disponibles"
                    ],
                    datasets: [
                        {
                            data: [ticketInfo.soldTickets, ticketInfo.availableTickets],
                            backgroundColor: ticketChartColors(),
                            hoverBackgroundColor: ticketChartColors(),
                            borderWidth: 5,
                            borderColor: () =>
                                $("html").hasClass("dark")
                                    ? getColor("darkmode.700")
                                    : getColor("white"),
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                },
            });
        }
    });
})();
