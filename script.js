// Toggle details visibility for the cards
function t(b) {
    const c = b.closest('.card');
    const d = c.querySelector('.det');
    d.classList.toggle('show');
    b.textContent = d.classList.contains('show') ? 'Hide Details' : 'View Details';
}

// Helper function to determine Judo Weight Category
function getWeightCategory(weight) {
    if (!weight || weight <= 0) return "N/A";
    
    // Logic: matches standard Judo weight brackets
    if (weight < 60) return "Under 60kg";
    if (weight >= 60 && weight < 66) return "60kg to 66kg";
    if (weight >= 66 && weight < 73) return "66kg to 73kg";
    if (weight >= 73 && weight < 81) return "73kg to 81kg";
    if (weight >= 81 && weight < 90) return "81kg to 90kg";
    if (weight >= 90 && weight < 100) return "90kg to 100kg";
    return "Over 100kg";
}

// Cost Calculation Logic
function calculateMonthlyTotal(weeklyFee, competitions, privateHours) {
    const monthlyTraining = weeklyFee * 4; // 4 weeks in a month
    const competitionCost = competitions * 220;
    const privateCost = privateHours * 90.5 * 4; // per week → monthly
    const total = monthlyTraining + competitionCost + privateCost;

    return {
        monthlyTraining,
        competitionCost,
        privateCost,
        total
    };
}

function calculateFee() {
    const firstName = document.querySelector('input[name="firstName"]').value.trim();
    const lastName = document.querySelector('input[name="lastName"]').value.trim();
    const name = (firstName + ' ' + lastName).trim() || 'Athlete';
    
    // Get weight and category
    const weightInput = document.querySelector('input[name="weight"]');
    const weight = Number(weightInput.value);
    const weightCategory = getWeightCategory(weight);

    const selectedCard = document.querySelector('.card.selected');
    if (!selectedCard) {
        document.getElementById('costResult').innerHTML =
            `<p class="text-2xl font-semibold text-gray-600">Please select a training plan</p>`;
        return;
    }

    const weeklyFee = Number(selectedCard.getAttribute('data-price'));
    const competitions = Number(document.getElementById('competitions').value) || 0;
    const privateHours = Number(document.getElementById('privateHours').value) || 0;

    const result = calculateMonthlyTotal(weeklyFee, competitions, privateHours);

    document.getElementById('costResult').innerHTML = `
        <div class="space-y-4">
            <p class="text-3xl font-bold text-red-700">Monthly Total for ${name}: <span class="text-black">AED ${result.total.toFixed(2)}</span></p>
            
            <div class="inline-block bg-red-100 text-red-700 px-6 py-2 rounded-full font-bold text-lg mb-4">
                Competition Category: ${weightCategory}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-lg mt-6">
                <div class="bg-blue-50 p-4 rounded-xl">
                    <p class="font-semibold">Training Plan</p>
                    <p>AED ${result.monthlyTraining.toFixed(2)}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-xl">
                    <p class="font-semibold">Competitions (${competitions})</p>
                    <p>AED ${result.competitionCost.toFixed(2)}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-xl">
                    <p class="font-semibold">Private Hours (${privateHours}/week)</p>
                    <p>AED ${result.privateCost.toFixed(2)}</p>
                </div>
            </div>
        </div>
    `;
}

// Initialize Event Listeners once the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Select training plan
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            const plan = this.getAttribute('data-plan');
            const price = this.getAttribute('data-price');
            document.getElementById('selectedPlan').value = plan;
            document.getElementById('planPrice').value = price;

            calculateFee();
        });
    });

    // Listeners for inputs
    const compInput = document.getElementById('competitions');
    const privateInput = document.getElementById('privateHours');
    const weightInput = document.querySelector('input[name="weight"]');
    const nameInputs = document.querySelectorAll('input[name="firstName"], input[name="lastName"]');

    if(compInput) compInput.addEventListener('input', calculateFee);
    if(privateInput) privateInput.addEventListener('input', calculateFee);
    if(weightInput) weightInput.addEventListener('input', calculateFee);
    
    nameInputs.forEach(input => {
        input.addEventListener('input', calculateFee);
    });
});