(function () {
	const storageKey = 'analytics_consent';
	const banner = document.getElementById('analytics-consent');
    const measurementId = 'G-2FZSE06H2R';
   
	function getConsent() {
		try {
			return localStorage.getItem(storageKey);
		} catch (error) {
			return null;
		}
	}

	function setConsent(value) {
		try {
			localStorage.setItem(storageKey, value);
		} catch (error) {
			// Consent will only last for this page load if storage is unavailable.
		}
	}

	function deleteAnalyticsCookies() {
		const cookieNames = document.cookie
			.split(';')
			.map(cookie => cookie.trim().split('=')[0])
			.filter(name => name === '_ga' || name.indexOf('_ga_') === 0);

		cookieNames.forEach(function (name) {
			const domains = [
				'',
				window.location.hostname,
				'.' + window.location.hostname
			];

			domains.forEach(function (domain) {
				const domainPart = domain ? '; domain=' + domain : '';

				document.cookie =
					name +
					'=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/' +
					domainPart +
					'; SameSite=Lax';
			});
		});
	}

	function acceptAnalytics() {
        localStorage.setItem('analytics_consent', 'accepted');

        gtag('consent', 'update', {
			analytics_storage: 'granted',
			ad_storage: 'granted',
			ad_user_data: 'granted',
			ad_personalization: 'granted',
			functionality_storage: 'granted',
			personalization_storage: 'granted'
		});

		document.getElementById('analytics-consent').hidden = true;
    }

	function declineAnalytics() {
        localStorage.setItem('analytics_consent', 'declined');

        gtag('consent', 'update', {
            analytics_storage: 'denied',
			ad_storage: 'denied',
			ad_user_data: 'denied',
			ad_personalization: 'denied',
			functionality_storage: 'denied',
			personalization_storage: 'denied'
        });

		document.getElementById('analytics-consent').hidden = true;
    }

	const consent = localStorage.getItem('analytics_consent');
	
    if (consent === 'accepted') {
		gtag('consent', 'update', {
			analytics_storage: 'granted',
			ad_storage: 'granted',
			ad_user_data: 'granted',
			ad_personalization: 'granted',
			functionality_storage: 'granted',
			personalization_storage: 'granted'
		});

		banner.hidden = true;
	} else if (consent === 'declined') {
		banner.hidden = true;
	} else {
		banner.hidden = false;
	} 

	document.addEventListener('click', function (event) {
		const button = event.target.closest('[data-analytics-consent]');

		if (!button) {
			return;
		}

		const choice = button.dataset.analyticsConsent;

		if (choice === 'accept') {
			acceptAnalytics();
		}

		if (choice === 'decline') {
			declineAnalytics();
		}
	});

    document.getElementById('analytics-settings')?.addEventListener('click', function () {
        document.getElementById('analytics-consent').hidden = false;
    });
})();