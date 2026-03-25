/**
 * India-only Location Autocomplete using Photon API
 */

document.addEventListener('DOMContentLoaded', function () {
    const locationInput = document.getElementById('locationInput');
    const suggestionsContainer = document.getElementById('locationSuggestions');
    
    if (!locationInput || !suggestionsContainer) return;

    let debounceTimer;
    let selectedIndex = -1;

    let isSelected = false;
    const form = locationInput.closest('form');

    locationInput.addEventListener('input', function () {
        isSelected = false; // Reset when user types
        this.classList.remove('is-valid');
        
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 3) {
            hideSuggestions();
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchLocations(query);
        }, 300);
    });

    locationInput.addEventListener('focus', function () {
        const query = this.value.trim();
        if (query.length >= 3 && suggestionsContainer.children.length > 0) {
            showSuggestions();
        }
    });

    // Enforce selection on form submit
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!isSelected && locationInput.value.trim() !== '') {
                e.preventDefault();
                e.stopPropagation();
                
                locationInput.classList.add('is-invalid');
                
                let errorDiv = locationInput.parentElement.parentElement.querySelector('.invalid-feedback');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback d-block';
                    locationInput.parentElement.parentElement.appendChild(errorDiv);
                }
                errorDiv.textContent = 'Please select a valid location from the suggestions.';
                errorDiv.style.display = 'block';
                
                locationInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    locationInput.addEventListener('keydown', function (e) {
        const items = suggestionsContainer.querySelectorAll('.location-suggestion-item:not(.no-results)');
        
        if (e.key === 'ArrowDown') {
            if (items.length > 0) {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % items.length;
                updateActiveItem(items);
            }
        } else if (e.key === 'ArrowUp') {
            if (items.length > 0) {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                updateActiveItem(items);
            }
        } else if (e.key === 'Enter') {
            if (selectedIndex > -1 && items[selectedIndex]) {
                e.preventDefault();
                selectItem(items[selectedIndex]);
            }
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function (e) {
        if (!locationInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideSuggestions();
        }
    });

    async function fetchLocations(query) {
        console.log('Fetching locations for:', query);
        try {
            // Correct Photon API parameters for biasing towards India (21, 78)
            const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=10&lat=21.1458&lon=79.0882&lang=en`;
            
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();

            // Filter for India only (using country property or countrycode)
            const filteredFeatures = (data.features || []).filter(f => {
                if (!f || !f.properties) return false;
                const props = f.properties;
                const country = props.country || '';
                const countryCode = props.countrycode || '';
                return country.toLowerCase() === 'india' || countryCode.toUpperCase() === 'IN';
            });

            renderSuggestions(filteredFeatures, query);
        } catch (error) {
            console.error('Error fetching locations:', error);
            hideSuggestions();
        }
    }

    function renderSuggestions(features, query) {
        suggestionsContainer.innerHTML = '';
        selectedIndex = -1;

        // 1. Render API Results
        features.forEach((feature, index) => {
            const props = feature.properties;
            
            const addressParts = [];
            if (props.name) addressParts.push(props.name);
            if (props.street && props.street !== props.name) addressParts.push(props.street);
            if (props.suburb) addressParts.push(props.suburb);
            if (props.locality) addressParts.push(props.locality);
            if (props.district) addressParts.push(props.district);
            if (props.city && props.city !== props.name) addressParts.push(props.city);
            if (props.state) addressParts.push(props.state);
            addressParts.push('India');

            const uniqueParts = [...new Set(addressParts)];
            const fullAddress = uniqueParts.join(', ');
            
            const displayTitle = props.name || props.street || props.suburb || props.city || 'Unknown Location';
            const displaySubtitle = uniqueParts.slice(1).join(', ');

            const item = document.createElement('div');
            item.className = 'location-suggestion-item';
            item.dataset.index = index;
            item.dataset.value = fullAddress;

            item.innerHTML = `
                <i class="fas fa-map-marker-alt"></i>
                <div class="location-suggestion-content">
                    <div class="location-suggestion-name">${displayTitle}</div>
                    <div class="location-suggestion-address">${displaySubtitle}</div>
                </div>
            `;

            item.addEventListener('click', () => selectItem(item));
            suggestionsContainer.appendChild(item);
        });

        // 2. Add "Custom Location" option at the bottom
        if (query && query.length >= 3) {
            const separator = document.createElement('div');
            separator.className = 'dropdown-divider my-1';
            suggestionsContainer.appendChild(separator);

            const customItem = document.createElement('div');
            customItem.className = 'location-suggestion-item custom-location-item';
            const customValue = query.toLowerCase().includes('india') ? query : `${query}, India`;
            customItem.dataset.value = customValue;

            customItem.innerHTML = `
                <i class="fas fa-keyboard"></i>
                <div class="location-suggestion-content">
                    <div class="location-suggestion-name">Use "${query}"</div>
                    <div class="location-suggestion-address">Custom India Location</div>
                </div>
            `;

            customItem.addEventListener('click', () => selectItem(customItem));
            suggestionsContainer.appendChild(customItem);
        }

        showSuggestions();
    }

    function updateActiveItem(items) {
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    function selectItem(item) {
        locationInput.value = item.dataset.value;
        isSelected = true;
        locationInput.classList.remove('is-invalid');
        locationInput.classList.add('is-valid');
        
        const errorDiv = locationInput.parentElement.parentElement.querySelector('.invalid-feedback');
        if (errorDiv) errorDiv.style.display = 'none';

        hideSuggestions();
        
        // Trigger validation
        const event = new Event('input', { bubbles: true });
        locationInput.dispatchEvent(event);
    }

    function showSuggestions() {
        suggestionsContainer.classList.remove('d-none');
    }

    function hideSuggestions() {
        suggestionsContainer.classList.add('d-none');
        selectedIndex = -1;
    }
});
