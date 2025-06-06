const wrapper = document.querySelector('.slider-wrapper');
  const items = document.querySelectorAll('.contenedor-general-informacion');
  let currentIndex = 0;

  document.getElementById('next').addEventListener('click', () => {
    if (currentIndex < items.length - 1) {
      currentIndex++;
      wrapper.style.transform = `translateX(-${currentIndex * 33.3}%)`;
    }
  });

  document.getElementById('prev').addEventListener('click', () => {
    if (currentIndex > 0) {
      currentIndex--;
      wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
    }
  });