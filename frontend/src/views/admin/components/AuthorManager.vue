<template>
  <div>
    <!-- Заголовок секции -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
          <i class="fas fa-tag text-[#c8a87c] text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-light text-[#2c2c2c]">Управление брендами</h3>
          <p class="text-xs text-[#8b7355] mt-0.5">Создание и редактирование брендов</p>
        </div>
      </div>
      <button 
        @click="openModal()" 
        class="px-5 py-2 rounded-xl bg-[#c8a87c] text-white text-sm hover:bg-[#b89a6e] transition-all flex items-center gap-2 shadow-sm"
      >
        <i class="fas fa-plus text-xs"></i>
        Добавить бренд
      </button>
    </div>

    <!-- Поиск -->
    <div class="mb-6">
      <div class="relative max-w-md">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#8b7355] text-sm"></i>
        <input 
          v-model="searchQuery"
          type="text"
          placeholder="Поиск брендов по названию..."
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
          @input="searchBrands"
        >
      </div>
    </div>

    <!-- Таблица брендов -->
    <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-[#e8e0d8]">
      <table class="w-full border-collapse">
        <thead>
          <tr class="border-b border-[#e8e0d8] bg-[#faf8f5]">
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Логотип</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Название</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Slug</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Товаров</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Страна</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && brands.length === 0">
            <td colspan="6" class="p-8 text-center">
              <div class="w-6 h-6 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
              <p class="mt-3 text-[#8b7355] text-sm">Загрузка...</p>
            </td>
          </tr>
          <tr v-else-if="brands.length === 0">
            <td colspan="6" class="p-8 text-center">
              <i class="fas fa-tags text-3xl text-[#e8e0d8] mb-2"></i>
              <p class="text-[#8b7355] text-sm">Бренды не найдены</p>
            </td>
          </tr>
          <tr v-for="brand in brands" :key="brand.id" class="border-b border-[#e8e0d8] hover:bg-[#faf8f5] transition-colors">
            <td class="p-4">
              <div class="w-10 h-10 rounded-full bg-[#faf8f5] border border-[#e8e0d8] flex items-center justify-center overflow-hidden">
                <img 
                  v-if="brand.photo" 
                  :src="getFullImageUrl(brand.photo)" 
                  :alt="brand.name"
                  class="w-full h-full object-cover"
                >
                <i v-else class="fas fa-building text-[#c8a87c]/30 text-lg"></i>
              </div>
            </td>
            <td class="p-4">
              <span class="text-[#2c2c2c] text-sm font-medium">{{ brand.name }}</span>
            </td>
            <td class="p-4 text-[#8b7355] text-sm font-mono">{{ brand.slug }}</td>
            <td class="p-4">
              <span class="px-2 py-1 rounded-full bg-[#faf8f5] text-[#8b7355] text-xs">
                {{ brand.products_count || 0 }}
              </span>
            </td>
            <td class="p-4 text-[#8b7355] text-sm">
              {{ brand.country || '-' }}
            </td>
            <td class="p-4">
              <div class="flex gap-2">
                <button 
                  @click="openModal(brand)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] hover:text-[#c8a87c] transition-all"
                  title="Редактировать"
                >
                  <i class="fas fa-edit text-xs"></i>
                </button>
                <button 
                  @click="deleteBrand(brand)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-red-400 hover:bg-red-50 hover:text-red-500 transition-all"
                  title="Удалить"
                >
                  <i class="fas fa-trash-alt text-xs"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
       </table>
    </div>

    <!-- Модальное окно для создания/редактирования бренда -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl transform transition-all animate-modal-slide max-h-[90vh] overflow-y-auto">
        <!-- Заголовок -->
        <div class="border-b border-[#e8e0d8] px-6 py-4 sticky top-0 bg-white z-10">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
              <i :class="editingBrand ? 'fas fa-edit' : 'fas fa-plus'" class="text-[#c8a87c] text-lg"></i>
            </div>
            <div>
              <h3 class="text-xl font-light text-[#2c2c2c]">
                {{ editingBrand ? 'Редактировать бренд' : 'Новый бренд' }}
              </h3>
              <p class="text-xs text-[#8b7355] mt-0.5">
                {{ editingBrand ? 'Измените данные бренда' : 'Заполните информацию о бренде' }}
              </p>
            </div>
          </div>
          <button @click="closeModal" class="absolute top-5 right-5 text-[#8b7355] hover:text-[#2c2c2c] transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        
        <form @submit.prevent="saveBrand" class="p-6">
          <div class="space-y-5">
            <!-- Логотип -->
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Логотип бренда</label>
              <div class="flex items-center gap-4">
                <div class="relative">
                  <div class="w-20 h-20 rounded-full bg-[#faf8f5] border border-[#e8e0d8] flex items-center justify-center overflow-hidden">
                    <img 
                      v-if="photoPreview" 
                      :src="photoPreview" 
                      alt="Preview"
                      class="w-full h-full object-cover"
                    >
                    <i v-else class="fas fa-building text-[#c8a87c]/30 text-3xl"></i>
                  </div>
                  <label class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-[#c8a87c] text-white flex items-center justify-center cursor-pointer hover:bg-[#b89a6e] transition-all shadow-sm">
                    <i class="fas fa-camera text-xs"></i>
                    <input 
                      type="file"
                      accept="image/*"
                      class="hidden"
                      @change="handlePhotoUpload"
                    >
                  </label>
                </div>
                <div class="flex-1">
                  <p class="text-xs text-[#8b7355]">Рекомендуемый размер: 200x200px</p>
                  <p class="text-[10px] text-[#8b7355] mt-1">JPEG, PNG, WebP. Максимум 2MB</p>
                  <button 
                    v-if="photoPreview"
                    type="button"
                    @click="removePhoto"
                    class="text-red-400 text-xs hover:text-red-500 mt-1"
                  >
                    Удалить логотип
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Название -->
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">
                Название бренда <span class="text-red-400">*</span>
              </label>
              <input 
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                :class="{ 'border-red-400': errors.name }"
                placeholder="Например: Gucci, Prada"
                required
                @input="generateSlug"
              >
              <p v-if="errors.name" class="text-red-400 text-xs mt-1">{{ errors.name[0] }}</p>
            </div>
            
            <!-- Slug -->
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">URL (Slug)</label>
              <div class="relative">
                <input 
                  v-model="form.slug"
                  type="text"
                  class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all font-mono"
                >
                <button 
                  type="button"
                  @click="generateSlug(true)"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8b7355] hover:text-[#c8a87c] text-xs"
                >
                  <i class="fas fa-sync-alt"></i>
                </button>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Страна -->
              <div>
                <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Страна</label>
                <input 
                  v-model="form.country"
                  type="text"
                  class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                  placeholder="Италия, Франция"
                >
              </div>
              
              <!-- Год основания -->
              <div>
                <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Год основания</label>
                <input 
                  v-model.number="form.year"
                  type="number"
                  class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                  placeholder="1921"
                  min="1000"
                  :max="new Date().getFullYear()"
                >
              </div>
            </div>
            
            <!-- Сайт -->
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Веб-сайт</label>
              <input 
                v-model="form.url_web"
                type="url"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                placeholder="https://www.gucci.com"
              >
            </div>
            
            <!-- Описание -->
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Описание бренда</label>
              <textarea 
                v-model="form.desc"
                rows="4"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all resize-none"
                placeholder="История бренда, философия, особенности..."
              ></textarea>
            </div>
            
            <!-- Кнопки -->
            <div class="flex gap-3 pt-4 border-t border-[#e8e0d8]">
              <button 
                type="submit"
                class="flex-1 px-6 py-2.5 rounded-xl bg-[#c8a87c] text-white text-sm hover:bg-[#b89a6e] transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                :disabled="loading"
              >
                <i v-if="loading" class="fas fa-spinner fa-spin text-xs"></i>
                {{ loading ? 'Сохранение...' : (editingBrand ? 'Сохранить' : 'Создать') }}
              </button>
              <button 
                type="button"
                @click="closeModal"
                class="px-6 py-2.5 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all"
              >
                Отмена
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { authorApi } from '@/api/authors'
import { useToast } from '@/composables/useToast'

const { toast, success, error } = useToast()

const brands = ref([])
const showModal = ref(false)
const editingBrand = ref(null)
const loading = ref(false)
const errors = ref({})
const searchQuery = ref('')
const searchTimeout = ref(null)
const photoFile = ref(null)
const photoPreview = ref('')

const form = ref({
  name: '',
  slug: '',
  desc: '',
  country: '',
  year: null,
  url_web: '',
  photo: null
})

const getFullImageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  return `${baseUrl}${path}`
}

const generateSlug = (force = false) => {
  if ((force || !form.value.slug) && form.value.name) {
    form.value.slug = form.value.name
      .toLowerCase()
      .replace(/[а-яё]/g, (char) => {
        const translit = {
          'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e',
          'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
          'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
          'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch', 'ъ': '',
          'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
        }
        return translit[char] || char
      })
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
      .replace(/-+/g, '-')
  }
}

watch(() => form.value.name, () => {
  if (!form.value.slug) generateSlug()
})

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  if (file.size > 2 * 1024 * 1024) {
    error('Файл превышает 2MB')
    return
  }
  
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    error('Поддерживаются: JPEG, PNG, WebP')
    return
  }
  
  photoFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => photoPreview.value = e.target.result
  reader.readAsDataURL(file)
}

const removePhoto = () => {
  photoFile.value = null
  photoPreview.value = ''
  form.value.photo = null
}

const searchBrands = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => loadBrands(), 500)
}

const loadBrands = async () => {
  try {
    const params = searchQuery.value ? { search: searchQuery.value } : {}
    const response = await authorApi.getAuthors(params)
    if (response.data.success) brands.value = response.data.data
  } catch (err) {
    console.error('Error loading brands:', err)
    error('Ошибка при загрузке брендов')
  }
}

const openModal = (brand = null) => {
  editingBrand.value = brand
  errors.value = {}
  photoFile.value = null
  photoPreview.value = ''
  
  if (brand) {
    form.value = {
      name: brand.name,
      slug: brand.slug,
      desc: brand.desc || '',
      country: brand.country || '',
      year: brand.year || null,
      url_web: brand.url_web || '',
      photo: brand.photo
    }
    if (brand.photo) photoPreview.value = getFullImageUrl(brand.photo)
  } else {
    form.value = {
      name: '', slug: '', desc: '', country: '', year: null, url_web: '', photo: null
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingBrand.value = null
  errors.value = {}
  photoFile.value = null
  photoPreview.value = ''
  form.value = { name: '', slug: '', desc: '', country: '', year: null, url_web: '', photo: null }
}

const saveBrand = async () => {
  loading.value = true
  errors.value = {}
  
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    if (form.value.slug) formData.append('slug', form.value.slug)
    if (form.value.desc) formData.append('desc', form.value.desc)
    if (form.value.country) formData.append('country', form.value.country)
    if (form.value.year) formData.append('year', form.value.year)
    if (form.value.url_web) formData.append('url_web', form.value.url_web)
    if (photoFile.value) formData.append('photo', photoFile.value)
    
    let response
    if (editingBrand.value) {
      response = await authorApi.updateAuthor(editingBrand.value.id, formData)
      if (response.data.success) success('Бренд обновлён')
    } else {
      response = await authorApi.createAuthor(formData)
      if (response.data.success) success('Бренд создан')
    }
    
    await loadBrands()
    closeModal()
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors
    error('Ошибка при сохранении бренда')
  } finally {
    loading.value = false
  }
}

const deleteBrand = async (brand) => {
  if (confirm(`Удалить бренд "${brand.name}"?`)) {
    try {
      const response = await authorApi.deleteAuthor(brand.id)
      if (response.data.success) {
        success('Бренд удалён')
        await loadBrands()
      }
    } catch (err) {
      error(err.response?.data?.message || 'Ошибка при удалении')
    }
  }
}

onMounted(() => loadBrands())
</script>

<style scoped>
.animate-modal-slide {
  animation: modalSlide 0.2s ease-out;
}
@keyframes modalSlide {
  from { opacity: 0; transform: scale(0.95) translateY(-10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>