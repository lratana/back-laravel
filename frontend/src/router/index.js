import { createRouter, createWebHistory } from 'vue-router'
import SignIn from '@com/auth/SignIn.vue'
import SignUp from '@com/auth/SignUp.vue'
import SignOut from '@com/auth/SignOut.vue'
import VerifyEmail from '@com/auth/VerifyEmail.vue'
import Dashboard from '@pg/Dashboard.vue'
import SetNewPassword from '@com/auth/SetNewPassword.vue'
import ResetPassword from '@com/auth/ResetPassword.vue'
import GoogleCallback from '@com/auth/GoogleCallback.vue'
import GoogleCallbackError from '@com/auth/GoogleCallbackError.vue'
import Navbar from '@com/includes/Navbar.vue'
import Sidebar from '@com/includes/SideBar.vue'
import Footer from '@com/includes/Footer.vue'
import UserProfile from '@com/auth/UserProfile.vue'
import Backup from '@pg/Backup.vue'


const includes = {
  navbar: Navbar,
  sidebar: Sidebar,
  footer: Footer,
}
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),


  routes: [
    {
      path: '/',
      name: 'auth.signin',
      component: SignIn,
      meta: { guard: false }
    },
    {
      path: '/signup',
      name: 'auth.signup',
      component: SignUp,
      meta: { guard: false }
    },
    {
      path: '/signout',
      name: 'auth.signout',
      component: SignOut,
      meta: { guard: true }
    },

    {
      path: '/email/verify/:api_url',
      name: 'auth.verify.email',
      component: VerifyEmail,
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      meta: { guard: true },
      components:
      {
        default: Dashboard,
        ...includes
      }
    },
    {
      path: '/profile',
      name: 'profile',
      meta: { guard: true },
      components:
      {
        default: UserProfile,
        ...includes
      }

    },
    {
      path: '/set-new-password',
      name: 'auth.setnewpassword',
      component: SetNewPassword,
    },
    {
      path: '/reset-password',
      name: 'auth.resetpassword',
      component: ResetPassword,
      meta: { guard: false }
    },
    {
      path: '/auth/google/callback',
      name: 'auth.google.callback',
      component: GoogleCallback,
    },
    {
      path: '/auth/google/callback/error',
      name: 'auth.google.callback.error',
      component: GoogleCallbackError,
    },
    {
      path: '/backups',
      name: 'backups',
      components: {
        default: Backup,
        ...includes,
      },
      meta: { guard: true },
    },
  ],
})

export default router
