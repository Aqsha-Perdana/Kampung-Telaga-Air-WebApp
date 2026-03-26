const hasChartData = () =>
    Boolean(
        window.revenueTrendData ||
        window.ordersByStatusData ||
        window.revenueByCategoryData
    );

const hasChartTargets = () =>
    Boolean(
        document.querySelector('#revenueChart') ||
        document.querySelector('#distributionChart') ||
        document.querySelector('#revenueByCategoryChart')
    );

const buildRevenueChartOptions = () => {
    const revenueData = window.revenueTrendData?.revenue ?? [];
    const avgRevenue = revenueData.length > 0
        ? revenueData.reduce((a, b) => a + b, 0) / revenueData.length
        : 0;

    return {
        series: [{ name: 'Revenue', data: revenueData }],
        chart: {
            type: 'area',
            height: 280,
            fontFamily: 'inherit',
            foreColor: '#adb0bb',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false,
                },
            },
        },
        colors: ['#6366f1'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.5,
                opacityTo: 0.05,
                stops: [0, 90, 100],
            },
        },
        grid: {
            borderColor: 'rgba(236, 240, 241, 0.6)',
            strokeDashArray: 4,
        },
        annotations: {
            yaxis: [{
                y: avgRevenue,
                borderColor: '#f59e0b',
                strokeDashArray: 4,
                label: {
                    borderColor: '#f59e0b',
                    style: {
                        color: '#fff',
                        background: '#f59e0b',
                        fontSize: '11px',
                        padding: { left: 6, right: 6, top: 2, bottom: 2 },
                    },
                    text: `Avg: RM ${avgRevenue.toLocaleString(undefined, { maximumFractionDigits: 0 })}`,
                    position: 'front',
                },
            }],
        },
        xaxis: {
            categories: window.revenueTrendData?.months ?? [],
            labels: {
                style: { cssClass: 'text-muted fill-current' },
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: {
            labels: {
                style: { cssClass: 'text-muted fill-current' },
                formatter: (val) => `RM ${val.toFixed(0)}`,
            },
        },
        tooltip: {
            theme: 'light',
            style: { fontSize: '12px' },
            x: { show: true },
            y: {
                formatter: (val) => `RM ${val.toFixed(2)}`,
            },
        },
    };
};

const buildDistributionChartOptions = () => {
    const labels = window.ordersByStatusData?.labels ?? [];
    const counts = window.ordersByStatusData?.counts ?? [];
    const totalOrders = counts.reduce((a, b) => a + b, 0);
    const statusColors = {
        paid: '#13DEB9',
        pending: '#FFAE1F',
        cancelled: '#FA896B',
        refunded: '#5D87FF',
        refund_requested: '#FFAE1F',
        completed: '#13DEB9',
        confirmed: '#49BEFF',
        failed: '#FA896B',
    };

    return {
        series: counts,
        chart: {
            type: 'donut',
            height: 220,
            fontFamily: 'inherit',
            foreColor: '#adb0bb',
        },
        labels: labels.map((s) => s.charAt(0).toUpperCase() + s.slice(1)),
        colors: labels.map((status) => statusColors[status.toLowerCase()] || '#adb0bb'),
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '13px',
                            fontWeight: 600,
                        },
                        value: {
                            show: true,
                            fontSize: '18px',
                            fontWeight: 700,
                            formatter: (val) => {
                                const pct = totalOrders > 0 ? ((val / totalOrders) * 100).toFixed(1) : 0;
                                return `${val} (${pct}%)`;
                            },
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '12px',
                            fontWeight: 600,
                            color: '#adb0bb',
                            formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0),
                        },
                    },
                },
            },
        },
        tooltip: {
            y: {
                formatter: (val) => {
                    const pct = totalOrders > 0 ? ((val / totalOrders) * 100).toFixed(1) : 0;
                    return `${val} orders (${pct}%)`;
                },
            },
        },
        legend: { show: false },
        stroke: { width: 2, colors: ['#fff'] },
    };
};

const buildCategoryChartOptions = () => ({
    series: window.revenueByCategoryData?.values ?? [],
    chart: {
        type: 'donut',
        height: 200,
        fontFamily: 'inherit',
        foreColor: '#adb0bb',
    },
    labels: window.revenueByCategoryData?.labels ?? [],
    colors: ['#06b6d4', '#f59e0b', '#10b981', '#ef4444'],
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '12px' },
                    value: {
                        show: true,
                        fontSize: '14px',
                        formatter: (val) => `RM ${parseFloat(val).toLocaleString()}`,
                    },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '12px',
                        formatter: (w) => `RM ${w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString()}`,
                    },
                },
            },
        },
    },
    stroke: { width: 0 },
    legend: { show: false },
    dataLabels: { enabled: false },
});

const initDashboardCharts = async () => {
    if (!hasChartData() || !hasChartTargets()) {
        return;
    }

    const { default: ApexCharts } = await import('apexcharts');

    const revenueChartEl = document.querySelector('#revenueChart');
    if (revenueChartEl && window.revenueTrendData) {
        const revenueChart = new ApexCharts(revenueChartEl, buildRevenueChartOptions());
        revenueChart.render();
    }

    const distributionChartEl = document.querySelector('#distributionChart');
    if (distributionChartEl && window.ordersByStatusData) {
        const distributionChart = new ApexCharts(distributionChartEl, buildDistributionChartOptions());
        distributionChart.render();
    }

    const revenueByCategoryEl = document.querySelector('#revenueByCategoryChart');
    if (revenueByCategoryEl && window.revenueByCategoryData) {
        const categoryChart = new ApexCharts(revenueByCategoryEl, buildCategoryChartOptions());
        categoryChart.render();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    initDashboardCharts().catch((error) => {
        // Keep dashboard functional even if chart library fails to load.
        console.error('Failed to initialize dashboard charts:', error);
    });
});
