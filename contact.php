<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us - E-Bikers</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <style>
      body {
        font-family: "Poppins", sans-serif;
      }
      .bg-lightblue {
        background-color: #4da8da;
      }
      .container {
        max-width: 600px;
      }
    </style>
  </head>

  <body>
    <nav class="navbar navbar-expand-lg bg-lightblue px-5">
      <div class="container">
        <a class="navbar-brand text-white fw-bold" href="#">E-Bikers</a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav mx-auto">
            <li class="nav-item mx-3">
              <a class="nav-link text-white fw-semibold" href="/e-bikers/"
                >Home</a
              >
            </li>
            <li class="nav-item mx-3">
              <a class="nav-link text-white fw-semibold" href="/e-bikers/contact"
                >Contact</a
              >
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <section class="container my-5">
      <h2 class="text-center fw-bold text-primary mb-4">Contact Us</h2>
      <p class="text-center">
        Have questions? Fill out the form below and we'll get back to you
        shortly!
      </p>

      <form id="contact-form" action="your-formspree-endpoint" method="POST">
        <div class="mb-3">
          <label for="name" class="form-label fw-semibold">Your Name</label>
          <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            required
          />
        </div>

        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">Your Email</label>
          <input
            type="email"
            class="form-control"
            id="email"
            name="email"
            required
          />
        </div>

        <div class="mb-3">
          <label for="message" class="form-label fw-semibold">Message</label>
          <textarea
            class="form-control"
            id="message"
            name="message"
            rows="4"
            required
          ></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Send Message</button>
      </form>

      <div id="success-message" class="alert alert-success mt-3 d-none">
        ✅ Your message has been sent successfully!
      </div>
    </section>

    <footer class="bg-dark text-white text-center py-3">
      <p>© 2025 E-Bikers. All Rights Reserved.</p>
    </footer>

    <script>
      document
        .getElementById("contact-form")
        .addEventListener("submit", function (e) {
          e.preventDefault();
          const form = e.target;
          const formData = new FormData(form);

          fetch(form.action, {
            method: "POST",
            body: formData,
            headers: { Accept: "application/json" },
          })
            .then((response) => {
              if (response.ok) {
                document.getElementById("success-message").classList.remove("d-none");
                form.reset();
              } else {
                alert("Something went wrong. Please try again.");
              }
            })
            .catch(() => alert("Error submitting the form."));
        });
    </script>
  </body>
</html>
