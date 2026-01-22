<template>
  <div class="login-page">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header text-center">
          <router-link to="/" class="h1"><b>Admin</b>LTE</router-link>
        </div>
        <div class="card-body">
          <p class="login-box-msg">Sign up for a new membership</p>
          <form>
            <div class="input-group mb-3">
              <input v-model="user.name" type="text" class="form-control" :class="{ 'is-invalid': userError.name }"
                placeholder="Name" />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-user"></span>
                </div>
              </div>
              <div v-if="userError.name" class="invalid-feedback">{{ userError.name }}</div>
            </div>
            <div class="input-group mb-3">
              <input v-model="user.email" type="email" class="form-control" :class="{ 'is-invalid': !!userError.email }"
                placeholder="Email" />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-envelope"></span>
                </div>
              </div>
              <div v-if="userError.email" class="invalid-feedback">{{ userError.email }}</div>
            </div>
            <div class="input-group mb-3">
              <input v-model="user.password" type="password" class="form-control"
                :class="{ 'is-invalid': !!userError.password }" placeholder="Password" />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
              <div v-if="userError.password" class="invalid-feedback">{{ userError.password }}</div>
            </div>
            <div class="input-group mb-3">
              <input v-model="user.password_confirmation" type="password" class="form-control"
                :class="{ 'is-invalid': !!userError.password_confirmation }" placeholder="Confirm Password" />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
              <div v-if="userError.password_confirmation" class="invalid-feedback">{{ userError.password_confirmation }}
              </div>
            </div>
            <div class="row">
              <div class="col-8"></div>
              <div class="col-4">
                <button type="submit" @click.prevent="funSignUp" class="btn btn-primary btn-block">Sign up</button>
              </div>
            </div>
          </form>
          <p class="mb-1">
            <router-link :to="{ name: 'auth.signin' }" class="text-center">I already have a membership</router-link>
          </p>
          <!-- Add this after the form and before the sign-in link -->
          <div class="social-auth-links text-center mt-2 mb-3">
            <button @click="googleSignUp" class="btn btn-block btn-danger">
              <i class="fab fa-google-plus mr-2"></i> Sign up using Google+
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>

import { reactive } from 'vue';
import { useRouter } from 'vue-router';
const router = useRouter();
import { postSignUp } from '@func/api/auth';
import { CloseModal, LoadingModal, MessageModal } from '@func/swal';
const user = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});
const userError = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});


// Add this function in the script setup section
function googleSignUp() {
  try {
    LoadingModal();
    window.location.href = `${window.API_URL}/auth/google`;
  } catch (error) {
    MessageModal("error", "Error", "Google sign-up failed");
  }
}

async function funSignUp() {
  const users = {
    name: user.name,
    email: user.email,
    password: user.password,
    password_confirmation: user.password_confirmation,
  };
  LoadingModal();
  try {

    const response = await postSignUp(users);
    console.log('Registration successful:', response.data);
    clearForm();
    Swal.fire({
      title: "Success",
      text: response.data.message,
      icon: "success",
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#d33",
      confirmButtonText: "Go to Sign In",
    }).then((result) => {
      if (result.isConfirmed) {
        router.push({ name: "auth.signin" });
      }
    });
  } catch (error) {
    if (!error.response) {

      return MessageModal('error', 'Network Error or Client code error.', 'error');
    }
    const errors = error.response.data.errors;
    Object.keys(userError).forEach((key) => {
      userError[key] = errors[key] ? errors[key][0] : '';
    });
    return CloseModal();
  }
};

const defaultUser = JSON.parse(JSON.stringify(user));
const defaultUserError = JSON.parse(JSON.stringify(userError));
async function clearForm() {
  Object.assign(user, defaultUser);
  Object.assign(userError, defaultUserError);
}
</script>