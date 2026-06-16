document.addEventListener('DOMContentLoaded', function () {
	const canvas = document.getElementById('greenhead-severity-chart-' + gh_location.location);

	if (!canvas || typeof Chart === 'undefined') {
		console.log('Error finding chart');
		return;
	}

	let activeDatasetIndex = null;

	const chartColors = [
		'#3366cc',
		'#dc3912',
		'#ff9900',
		'#000000',
		'#990099',
		'#0099c6',
		'#dd4477',
		'#66aa00'
	];

	function rgba(hex, alpha) {
		hex = hex.replace('#', '');

		const r = parseInt(hex.substring(0, 2), 16);
		const g = parseInt(hex.substring(2, 4), 16);
		const b = parseInt(hex.substring(4, 6), 16);

		return `rgba(${r}, ${g}, ${b}, ${alpha})`;
	}

	function setActiveDataset(chart, datasetIndex) {
		if (activeDatasetIndex === datasetIndex) {
			return;
		}

		activeDatasetIndex = datasetIndex;

		chart.data.datasets.forEach((dataset, index) => {
			dataset.pointRadius = 0;
			dataset.pointHoverRadius = 0;
			dataset.pointHitRadius = 0;
			dataset.hitRadius = 0;
			dataset.borderWidth = 2;

			if (datasetIndex === null) {
				dataset.borderColor = dataset._baseColor;
				dataset.backgroundColor = dataset._baseColor;
			} else {
				const active = index === datasetIndex;

				dataset.borderColor = active
					? dataset._baseColor
					: rgba(dataset._baseColor, 0.15);

				dataset.backgroundColor = active
					? dataset._baseColor
					: rgba(dataset._baseColor, 0.15);
			}
		});

		chart.update('none');
	}

	gh_location.datasets.forEach((dataset, index) => {
		const color = chartColors[index % chartColors.length];

		dataset.borderColor = color;
		dataset.backgroundColor = color;
		dataset._baseColor = color;

		dataset.borderWidth = 2;
		dataset.tension = 0.25;
		dataset.spanGaps = true;

		dataset.pointRadius = 0;
		dataset.pointHoverRadius = 0;
		dataset.pointHitRadius = 0;
		dataset.hitRadius = 0;
	});

	new Chart(canvas, {
		type: 'line',
		data: {
			labels: gh_location.labels,
			datasets: gh_location.datasets
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			interaction: {
				mode: 'index',
				intersect: false
			},
			elements: {
				line: {
					borderWidth: 2
				},
				point: {
					radius: 0,
					hoverRadius: 0,
					hitRadius: 0
				}
			},
			datasets: {
				line: {
					pointRadius: 0,
					pointHoverRadius: 0,
					pointHitRadius: 0,
					radius: 0,
					hoverRadius: 0,
					hitRadius: 0
				}
			},
			scales: {
				y: {
					min: 0,
					max: 4,
					ticks: {
						stepSize: 1
					},
					title: {
						display: true,
						text: 'Average Severity'
					}
				},
				x: {
					ticks: {
						maxTicksLimit: 12
					}
				}
			},
			plugins: {
				legend: {
					onClick: function(event, legendItem, legend) {
						const chart = legend.chart;
						const index = legendItem.datasetIndex;
						const meta = chart.getDatasetMeta(index);

						meta.hidden = meta.hidden === null
							? !chart.data.datasets[index].hidden
							: null;

						activeDatasetIndex = null;

						chart.data.datasets.forEach((dataset) => {
							dataset.pointRadius = 0;
							dataset.pointHoverRadius = 0;
							dataset.pointHitRadius = 0;
							dataset.hitRadius = 0;
							dataset.borderWidth = 2;
							dataset.borderColor = dataset._baseColor;
							dataset.backgroundColor = dataset._baseColor;
						});

						chart.update('none');
					},
					onHover: function(event, legendItem, legend) {
						setActiveDataset(legend.chart, legendItem.datasetIndex);
					},
					onLeave: function(event, legendItem, legend) {
						setActiveDataset(legend.chart, null);
					}
				},
				tooltip: {
					callbacks: {
						label: function(context) {
							if (context.raw === null) {
								return context.dataset.label + ': no data';
							}

							return context.dataset.label + ': ' + context.raw.toFixed(2);
						}
					}
				}
			},
			onHover: function(event, activeElements, chart) {
				/*if (!activeElements.length) {
					setActiveDataset(chart, null);
					return;
				}

				setActiveDataset(chart, activeElements[0].datasetIndex);*/
			}
		}
	});
});