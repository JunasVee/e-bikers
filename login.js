const clerkKey = window.CLERK_API;

const clerk = new Clerk(clerkKey);

clerk.load().then(() => {
    if (clerk.user) {
        document.getElementById('app').innerHTML = `<div id="user-button"></div>`;
        clerk.mountUserButton(document.getElementById('user-button'));
    } else {
        document.getElementById('app').innerHTML = `<div id="sign-in"></div>`;
        clerk.mountSignIn(document.getElementById('sign-in'));
    }
});
