@extends('layouts.app')

@section('body_class', 'sign-in-page')

@section('content')
<section class="sign-in letters-spaced w-75 position-relative m-auto">
  <div class="container bg-white my-15 rounded-2">
    <div class="row p-5">
      <div class="col-12 col-xl-6 pe-xl-7 mb-4 mb-xl-0 d-flex flex-column gap-4">
        <h2 class="fs-lg grotesk-mono-reg mb-3">Sign in</h2>
        <form method="POST" action="{{ route('login') }}" class="d-flex flex-column gap-4">
          @csrf
          <div class="form-wrap d-flex flex-column gap-2">
            <label for="email-signin">Email</label>
            <input type="email" id="email-signin" name="email" class="w-100 px-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email')<span class="text-danger fs-sm" role="alert">{{ $message }}</span>@enderror
          </div>
          <div class="form-wrap d-flex flex-column gap-2">
            <label for="password-signin" class="d-flex justify-content-between">Password <button type="button" class="password-toggle border-0 bg-transparent p-0 fs-sm" aria-controls="password-signin" aria-label="Show password"><i class="bi bi-eye-slash-fill" aria-hidden="true"></i> <span>Show</span></button></label>
            <input type="password" id="password-signin" name="password" class="w-100 px-3 @error('password') is-invalid @enderror" autocomplete="current-password" required>
            @error('password')<span class="text-danger fs-sm" role="alert">{{ $message }}</span>@enderror
          </div>
          <label class="d-flex gap-2 align-items-center" for="remember-signin"><input type="checkbox" id="remember-signin" name="remember" value="1" @checked(old('remember'))> Remember me</label>
          <button type="submit" class="w-100 submit letters-spaced py-3 bg-purple text-white fs-lg">SUBMIT</button>
        </form>
      </div>
      <div class="col-12 col-xl-6 ps-xl-7 mt-5 mt-xl-0 position-relative request letters-spaced d-flex flex-column">
        <h2 class="fs-lg grotesk-mono-reg mb-3">Request an account</h2>
        <p class="pe-2">Archive accounts are provided by the Sing Sing Prison Museum team. Contact an administrator to request access.</p>
        <ul class="mt-3 pe-5">
          <li class="my-2">Tell the museum how you plan to contribute to the archive.</li>
          <li class="my-2">Describe any stories or materials you would like to share.</li>
          <li class="my-2">Include your contact details so the team can respond.</li>
        </ul>
        <a class="request-account-button w-100 py-3 px-3 letters-spaced bg-grey-dark text-white fs-lg text-center text-decoration-none" href="https://www.singsingprisonmuseum.org/contact.html">REQUEST AN ACCOUNT</a>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  document.querySelector('.password-toggle')?.addEventListener('click', function () {
    const input = document.getElementById('password-signin');
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    this.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
    this.querySelector('span').textContent = visible ? 'Hide' : 'Show';
    this.querySelector('i').className = visible ? 'bi bi-eye-fill' : 'bi bi-eye-slash-fill';
  });
</script>
@endpush
