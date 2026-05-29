import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/HomeView.vue')
    },
    {
      path: '/catalog',
      name: 'catalog',
      component: () => import('@/views/CatalogView.vue')
    },
    {
      path: '/product/:id',
      name: 'product',
      component: () => import('@/views/ProductView.vue')
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { guest: true } // Только для гостей
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue'),
      meta: { guest: true } // Только для гостей
    },

    {
      path: '/auth/yandex/callback',
      name: 'yandex-callback',
      component: () => import('@/views/auth/YandexCallbackView.vue'),
      meta: { title: 'Авторизация через Яндекс' }
    },
    
    {
      path: '/cart',
      name: 'cart',
      component: () => import('@/views/CartView.vue')
    },
    {
      path: '/checkout',
      name: 'checkout',
      component: () => import('@/views/CheckoutView.vue'),
      meta: { requiresAuth: true } // Требует авторизации
    },
    {
      path: '/account',
      name: 'account',
      component: () => import('@/views/AccountView.vue'),
      meta: { requiresAuth: true } // Требует авторизации
    },
    {
      path: '/orders',
      name: 'orders',
      component: () => import('@/views/OrdersView.vue'),
      meta: { requiresAuth: true } // Требует авторизации
    },

    {
      path: '/orders/:id',
      name: 'order-detail',
      component: () => import('@/views/OrderDetailView.vue')
    },

    {
      path: '/favorites',
      name: 'favorites',
      component: () => import('@/views/FavoritesView.vue'),
      meta: { requiresAuth: true } // Требует авторизации
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('@/views/AboutView.vue')
    },
    {
      path: '/collections',
      name: 'collections',
      component: () => import('@/views/CollectionsView.vue')
    },
    {
      path: '/authors',
      name: 'authors',
      component: () => import('@/views/AuthorsView.vue')
    },
    {
      path: '/authors/:id',
      name: 'author',
      component: () => import('@/views/AuthorView.vue')
    },
    {
      path: '/rare-books',
      name: 'rare-books',
      component: () => import('@/views/RareBooksView.vue')
    },
    {
      path: '/bookcrossing',
      name: 'bookcrossing',
      component: () => import('@/views/BookcrossingView.vue')
    },
    {
      path: '/author-meetings',
      name: 'author-meetings',
      component: () => import('@/views/AuthorMeetingsView.vue')
    },
    {
      path: '/delivery-payment',
      name: 'delivery-payment',
      component: () => import('@/views/DeliveryPaymentView.vue')
    },
    {
      path: '/contacts',
      name: 'contacts',
      component: () => import('@/views/ContactsView.vue')
    },
    {
      path: '/faq',
      name: 'faq',
      component: () => import('@/views/FAQView.vue')
    },
    {
      path: '/sitemap',
      name: 'sitemap',
      component: () => import('@/views/SitemapView.vue')
    },
    {
      path: '/tracking',
      name: 'tracking',
      component: () => import('@/views/TrackingView.vue')
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('@/views/admin/AdminView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true } // Требует авторизацию и права админа
    }
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0, behavior: 'smooth' }
    }
  }
})

// Навигационный хук для защиты маршрутов
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Проверяем авторизацию при каждом переходе, если еще не проверяли
  if (!authStore.isAuthenticated && !authStore.user) {
    await authStore.checkAuth()
  }
  
  // Проверка на авторизацию
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ 
      path: '/login', 
      query: { redirect: to.fullPath } // Сохраняем путь для перенаправления после входа
    })
  }
  // Проверка на права администратора
  else if (to.meta.requiresAdmin && authStore.user?.role !== 'admin' && authStore.user?.role !== 'manager') {
    next('/') // Перенаправляем на главную, если не админ и не менеджер
  }
  // Проверка на гостевые маршруты (не пускаем авторизованных на страницы логина/регистрации)
  else if (to.meta.guest && authStore.isAuthenticated) {
    // Перенаправляем админов на админ-панель, обычных пользователей на главную
    if (authStore.user?.role === 'admin') {
      next('/admin')
    } else {
      next('/')
    }
  }
  else {
    next()
  }
})

export default router