// --- ELEMENTS ---
const changeTEXT = document.getElementById('change');
const movieDesc = document.querySelector('.text p'); // paragraph inside .text
const Searchbtn = document.getElementById('sea');
const CloseSearchbtn = document.querySelector('.cl');
const SearchContainer = document.querySelector('.sea');
const popContainer = document.querySelector('.pops .pop');
const leftArrow = document.querySelector('.left');
const rightArrow = document.querySelector('.right');
const watchBtn = document.querySelector('.b a:first-child'); // first "Watch" button
const loader = document.querySelector('.loader');
setTimeout(() => {
  loader.style.display = 'none';
}, 7000);
// --- MOVIES DATA ---
const movies = {
  o1: {
    title: 'Night Has Come',
    desc: 'A thrilling mystery unfolds as darkness brings unexpected truths to light.'
  },
  o2: {
    title: 'Jumanji',
    desc: 'Enter the wild world of Jumanji, where adventure and chaos collide in every roll of the dice.'
  },
  o3: {
    title: 'Feel Me in Your Pain',
    desc: 'A story of love, loss, and redemption that reaches into the deepest corners of your heart.'
  },
  o4: {
    title: 'WAR II',
    desc: 'Explosive action meets raw emotion in this battle for freedom and survival.'
  },
  o5: {
    title: 'Squid Game 2',
    desc: 'The deadly games return — with higher stakes, darker secrets, and desperate players.'
  }
};

// --- ACTIVE MOVIE TRACKER ---
let activeMovie = 'o3'; // default (the center one)

// --- HANDLE MOVIE HOVER EFFECTS ---
for (let key in movies) {
  const el = document.getElementById(key);
  el.addEventListener('mouseenter', () => {
    changeTEXT.textContent = movies[key].title;
    movieDesc.textContent = movies[key].desc;
    activeMovie = key;
  });
}

// --- SEARCH BOX TOGGLE ---
Searchbtn.addEventListener('click', () => {
  SearchContainer.style.transform = 'translate(0)';
});
CloseSearchbtn.addEventListener('click', () => {
  SearchContainer.style.transform = 'translateY(200%)';
});

// --- POPULAR SECTION: DUPLICATE TEST ITEMS ---
// 

// --- POPULAR SCROLL ARROWS ---
rightArrow.addEventListener('click', () => {
  popContainer.scrollBy({ left: 400, behavior: 'smooth' });
});
leftArrow.addEventListener('click', () => {
  popContainer.scrollBy({ left: -400, behavior: 'smooth' });
});

// --- WATCH BUTTON ACTION ---
watchBtn.addEventListener('click', (e) => {
  e.preventDefault();
  window.location.href = `watch.html?movie=${encodeURIComponent(movies[activeMovie].title)}`;
});




// Search toggle
const seaBtn = document.getElementById("sea");
const seaBox = document.querySelector(".sea");
const closeBtn = document.querySelector(".sea .cl");

seaBtn.addEventListener("click", () => {
  seaBox.style.transform = "translateY(0)";
});
closeBtn.addEventListener("click", () => {
  seaBox.style.transform = "translateY(200%)";
});

// Search functionality for movies in index page
const searchInput = document.querySelector(".sea input");
const allMovies = document.querySelectorAll(".ses .se");

searchInput.addEventListener("input", () => {
  const filter = searchInput.value.toLowerCase();
  allMovies.forEach((movie) => {
    const name = movie.getAttribute("data-name") || "";
    if (name.toLowerCase().includes(filter)) {
      movie.style.display = "block";
    } else {
      movie.style.display = "none";
    }
  });
});
