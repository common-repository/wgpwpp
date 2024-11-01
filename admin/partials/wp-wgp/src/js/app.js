const flag = document.querySelector('.flag')
const faq = document.querySelector('#faq ul');
const modal = document.querySelector('#overlay');
const modal_close = document.querySelector('#modal svg');
const modal_content = document.querySelector('#modal div');


flag.addEventListener( 'click', () => {
    faq.classList.toggle('hidden');
    modal.style.visibility = 'hidden';
})

modal_close.addEventListener('click', () => {
    modal.style.visibility = 'hidden';
});

faq.querySelectorAll('li > a').forEach(item => {
    item.addEventListener('click', (evt) => {
        evt.preventDefault();

        let faq_content = item.querySelector('div');
        modal_content.innerHTML = faq_content.innerHTML;

        faq.classList.toggle('hidden');
        modal.style.visibility = 'visible';
    })
})