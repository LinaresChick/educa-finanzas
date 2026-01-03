const signUp = document.getElementById('sign-up');
const signIn = document.getElementById('sign-in');
const loginIn = document.getElementById('login-in');
const loginUp = document.getElementById('login-up');

signUp.addEventListener('click', () => {
    loginIn.classList.add('none');
    setTimeout(()=>loginUp.classList.remove('none'),150);
});

signIn.addEventListener('click', () => {
    loginUp.classList.add('none');
    setTimeout(()=>loginIn.classList.remove('none'),150);
});
