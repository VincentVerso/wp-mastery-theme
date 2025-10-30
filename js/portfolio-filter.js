/**
 * Portfolio Filter Functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');

    // Make sure filter buttons exist before running the code
    if (!filterBtns.length) {
        return;
    }

    // Add fadeIn animation keyframes to the document's head
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);


    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove 'active' class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));

            // Add 'active' class to the clicked button
            this.classList.add('active');

            // Get the filter value from the data-filter attribute
            const filterValue = this.getAttribute('data-filter');

            // Filter the portfolio items
            portfolioItems.forEach(item => {
                // If "All" is selected, show all items
                if (filterValue === '*') {
                    item.style.display = 'block';
                    item.style.animation = 'fadeIn 0.5s ease';
                } else {
                    // Check if the item has the class matching the filter
                    if (item.classList.contains(filterValue.replace('.', ''))) {
                        item.style.display = 'block';
                        item.style.animation = 'fadeIn 0.5s ease';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    });
});

// Create a <style> element in memory
const style = document.createElement('style');

// Define the CSS keyframe animation as its text content
style.textContent = `
    @keyframes fadeIn {
        from { 
            opacity: 0; 
            transform: translateY(20px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }
`;

// Add the new <style> element to the <head> of the document
document.head.appendChild(style);