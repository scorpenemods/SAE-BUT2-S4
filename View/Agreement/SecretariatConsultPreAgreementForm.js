// Récupération des éléments du DOM
const modal = document.getElementById("preAgreementModal");
const openModalButton = document.getElementById("PreAgreement");
const closeModalButton = document.querySelector(".close-button");
const searchBar = document.getElementById("searchBar-student");
const studentList = document.getElementById("studentList");

// Fonction pour afficher le modal
openModalButton.addEventListener("click", () => {
modal.style.display = "block";
});

// Fonction pour fermer le modal
closeModalButton.addEventListener("click", () => {
modal.style.display = "none";
});

// Fonction pour fermer le modal en cliquant en dehors du contenu
window.addEventListener("click", (event) => {
if (event.target === modal) {
modal.style.display = "none";
}
});

// Recherche dans la liste des élèves
searchBar.addEventListener("input", (event) => {
const filter = event.target.value.toLowerCase();
const listItems = studentList.getElementsByTagName("li");

Array.from(listItems).forEach((item) => {
const text = item.textContent || item.innerText;
item.style.display = text.toLowerCase().includes(filter) ? "" : "none";
});
});

// Gestion du second modal
const modalToValidate = document.getElementById("preAgreementToValidateModal");
const openModalButtonToValidate = document.getElementById("PreAgreementToValidate");
const closeModalButtonToValidate = modalToValidate.querySelector(".close-button");
const searchBarToValidate = document.getElementById("searchBarToValidate");
const studentListToValidate = document.getElementById("studentListToValidate");

openModalButtonToValidate.addEventListener("click", () => {
modalToValidate.style.display = "block";
});

closeModalButtonToValidate.addEventListener("click", () => {
modalToValidate.style.display = "none";
});

window.addEventListener("click", (event) => {
if (event.target === modalToValidate) {
modalToValidate.style.display = "none";
}
});

searchBarToValidate.addEventListener("input", (event) => {
const filter = event.target.value.toLowerCase();
const listItems = studentListToValidate.getElementsByTagName("li");

Array.from(listItems).forEach((item) => {
const text = item.textContent || item.innerText;
item.style.display = text.toLowerCase().includes(filter) ? "" : "none";
});
});

