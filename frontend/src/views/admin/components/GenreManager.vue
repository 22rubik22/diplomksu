<template>
  <div>
    <!-- Заголовок секции -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
          <i class="fas fa-folder-tree text-[#c8a87c] text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-light text-[#2c2c2c]">Управление категориями</h3>
          <p class="text-xs text-[#8b7355] mt-0.5">Создание и редактирование категорий товаров</p>
        </div>
      </div>
      <button 
        @click="openModal()" 
        class="px-5 py-2 rounded-xl bg-[#c8a87c] text-white text-sm hover:bg-[#b89a6e] transition-all flex items-center gap-2 shadow-sm"
      >
        <i class="fas fa-plus text-xs"></i>
        Добавить категорию
      </button>
    </div>

    <!-- Поиск -->
    <div class="mb-6">
      <div class="relative max-w-md">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#8b7355] text-sm"></i>
        <input 
          v-model="searchQuery"
          type="text"
          placeholder="Поиск категорий..."
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
          @input="searchGenres"
        >
      </div>
    </div>

    <!-- Таблица категорий -->
    <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-[#e8e0d8]">
      <table class="w-full border-collapse">
        <thead>
          <tr class="border-b border-[#e8e0d8] bg-[#faf8f5]">
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Изображение</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Название</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Slug</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Родитель</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Статус</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && genres.length === 0">
            <td colspan="8" class="p-8 text-center">
              <div class="w-6 h-6 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
              <p class="mt-3 text-[#8b7355] text-sm">Загрузка...</p>
            </td>
          </tr>
          <tr v-else-if="genres.length === 0">
            <td colspan="8" class="p-8 text-center">
              <i class="fas fa-folder-open text-3xl text-[#e8e0d8] mb-2"></i>
              <p class="text-[#8b7355] text-sm">Категории не найдены</p>
            </td>
          </tr>
          <tr v-for="genre in genres" :key="genre.id" class="border-b border-[#e8e0d8] hover:bg-[#faf8f5] transition-colors">
            <td class="p-4">
              <div class="w-10 h-10 rounded-lg bg-[#faf8f5] border border-[#e8e0d8] flex items-center justify-center overflow-hidden">
                <img 
                  v-if="genre.image_url" 
                  :src="genre.image_url" 
                  :alt="genre.name"
                  class="w-full h-full object-cover"
                >
                <i v-else class="fas fa-folder text-[#c8a87c]/30 text-lg"></i>
              </div>
            </td>
            <td class="p-4">
              <span class="text-[#2c2c2c] text-sm font-medium">{{ genre.name }}</span>
            </td>
            <td class="p-4 text-[#8b7355] text-sm font-mono">{{ genre.slug }}</td>
            <td class="p-4">
              <span v-if="genre.parent" class="text-sm text-[#c8a87c]">
                <i class="fas fa-folder-open mr-1 text-xs"></i>
                {{ genre.parent.name }}
              </span>
              <span v-else class="text-sm text-[#8b7355]">—</span>
            </td>
            <td class="p-4">
              <button 
                @click="toggleActive(genre)"
                class="px-2 py-1 rounded-full text-[10px] font-medium transition-all"
                :class="genre.is_active ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-400'"
              >
                {{ genre.is_active ? 'Активна' : 'Неактивна' }}
              </button>
            </td>
            <td class="p-4">
              <div class="flex gap-2">
                <button 
                  @click="openModal(genre)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] hover:text-[#c8a87c] transition-all"
                  title="Редактировать"
                >
                  <i class="fas fa-edit text-xs"></i>
                </button>
                <button 
                  @click="deleteGenre(genre)" 
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

    <!-- Модальное окно для создания/редактирования категории -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl transform transition-all animate-modal-slide">
        <!-- Заголовок -->
        <div class="border-b border-[#e8e0d8] px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
              <i :class="editingGenre ? 'fas fa-edit' : 'fas fa-plus'" class="text-[#c8a87c] text-lg"></i>
            </div>
            <div>
              <h3 class="text-xl font-light text-[#2c2c2c]">
                {{ editingGenre ? 'Редактировать категорию' : 'Новая категория' }}
              </h3>
              <p class="text-xs text-[#8b7355] mt-0.5">
                {{ editingGenre ? 'Измените данные категории' : 'Заполните информацию о категории' }}
              </p>
            </div>
          </div>
          <button @click="closeModal" class="absolute top-5 right-5 text-[#8b7355] hover:text-[#2c2c2c] transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        
        <form @submit.prevent="saveGenre" class="p-6">
          <div class="space-y-5">
            <!-- Название -->
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">
                Название <span class="text-red-400">*</span>
              </label>
              <input 
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                :class="{ 'border-red-400': errors.name }"
                placeholder="Например: Сумки"
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
            
            <!-- Родительская категория -->
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Родительская категория</label>
              <select v-model="form.parent_id" class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer">
                <option :value="null">— Нет (корневая категория) —</option>
                <option v-for="genre in genreOptions" :key="genre.id" :value="genre.id">
                  <span v-for="i in genre.level" :key="i"> </span>
                  {{ genre.name }}
                </option>
              </select>
            </div>
            
            <!-- Изображение -->
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Изображение категории</label>
              
              <div v-if="form.image_preview || form.existing_image_url" class="mb-3">
                <div class="relative inline-block">
                  <img 
                    :src="form.image_preview || form.existing_image_url" 
                    alt="Preview"
                    class="w-20 h-20 object-cover rounded-xl border border-[#e8e0d8]"
                  >
                  <button 
                    type="button"
                    @click="removeImage"
                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition flex items-center justify-center text-xs"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              
              <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all">
                <i class="fas fa-upload text-xs"></i>
                Выбрать изображение
                <input 
                  type="file"
                  accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                  class="hidden"
                  @change="handleImageUpload"
                >
              </label>
              <p class="text-[10px] text-[#8b7355] mt-2">JPEG, PNG, GIF, WebP. Максимум 2MB</p>
              <p v-if="errors.image" class="text-red-400 text-xs mt-1">{{ errors.image[0] }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <!-- Порядок сортировки -->
              <div>
                <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Порядок</label>
                <input 
                  v-model.number="form.sort_order"
                  type="number"
                  class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                  min="0"
                >
              </div>
              
              <!-- Статус -->
              <div>
                <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Статус</label>
                <select v-model="form.is_active" class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer">
                  <option :value="true">Активна</option>
                  <option :value="false">Неактивна</option>
                </select>
              </div>
            </div>
            
            <!-- Описание -->
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Описание</label>
              <textarea 
                v-model="form.description"
                rows="3"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all resize-none"
                placeholder="Краткое описание категории..."
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
                {{ loading ? 'Сохранение...' : (editingGenre ? 'Сохранить' : 'Создать') }}
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
import { genreApi } from '@/api/genres'
import { useToast } from '@/composables/useToast'

const { toast, success, error } = useToast()

const genres = ref([])
const showModal = ref(false)
const editingGenre = ref(null)
const loading = ref(false)
const errors = ref({})
const searchQuery = ref('')
const searchTimeout = ref(null)
const imageFile = ref(null)

const form = ref({
  name: '',
  slug: '',
  parent_id: null,
  description: '',
  sort_order: 0,
  is_active: true,
  image: null,
  existing_image_url: null,
  image_preview: null,
  delete_image: false
})

const genreOptions = ref([])

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  if (file.size > 2 * 1024 * 1024) {
    error('Файл превышает 2MB')
    return
  }
  
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    error('Поддерживаются: JPEG, PNG, GIF, WebP')
    return
  }
  
  imageFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => form.value.image_preview = e.target.result
  reader.readAsDataURL(file)
  if (errors.value.image) delete errors.value.image
}

const removeImage = () => {
  imageFile.value = null
  form.value.image_preview = null
  form.value.existing_image_url = null
  form.value.image = null
  if (editingGenre.value && editingGenre.value.image_url) form.value.delete_image = true
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

const searchGenres = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => loadGenres(), 500)
}

const loadGenres = async () => {
  try {
    const params = searchQuery.value ? { search: searchQuery.value } : {}
    const response = await genreApi.getGenres(params)
    if (response.data.success) {
      genres.value = response.data.data
      buildGenreOptions()
    }
  } catch (err) {
    console.error('Error loading genres:', err)
    error('Ошибка при загрузке категорий')
  }
}

const buildGenreOptions = () => {
  const options = []
  const addGenres = (items, level = 0) => {
    items.forEach(item => {
      if (editingGenre.value && item.id === editingGenre.value.id) return
      options.push({ ...item, level })
      if (item.children?.length) addGenres(item.children, level + 1)
    })
  }
  const rootGenres = genres.value.filter(g => !g.parent_id)
  addGenres(rootGenres)
  genreOptions.value = options
}

const openModal = (genre = null) => {
  editingGenre.value = genre
  errors.value = {}
  imageFile.value = null
  
  if (genre) {
    form.value = {
      name: genre.name,
      slug: genre.slug,
      parent_id: genre.parent_id,
      description: genre.description || '',
      sort_order: genre.sort_order,
      is_active: genre.is_active,
      image: null,
      existing_image_url: genre.image_url || null,
      image_preview: genre.image_url || null,
      delete_image: false
    }
  } else {
    form.value = {
      name: '',
      slug: '',
      parent_id: null,
      description: '',
      sort_order: 0,
      is_active: true,
      image: null,
      existing_image_url: null,
      image_preview: null,
      delete_image: false
    }
  }
  showModal.value = true
  buildGenreOptions()
}

const closeModal = () => {
  showModal.value = false
  editingGenre.value = null
  errors.value = {}
  imageFile.value = null
  form.value = {
    name: '', slug: '', parent_id: null, description: '',
    sort_order: 0, is_active: true, image: null,
    existing_image_url: null, image_preview: null, delete_image: false
  }
}

const saveGenre = async () => {
  loading.value = true
  errors.value = {}
  
  try {
    let data
    if (imageFile.value) {
      data = new FormData()
      data.append('name', form.value.name)
      if (form.value.slug) data.append('slug', form.value.slug)
      if (form.value.parent_id) data.append('parent_id', form.value.parent_id)
      if (form.value.description) data.append('description', form.value.description)
      data.append('sort_order', form.value.sort_order)
      data.append('is_active', form.value.is_active ? '1' : '0')
      data.append('image', imageFile.value)
    } else {
      data = {
        name: form.value.name,
        slug: form.value.slug || null,
        parent_id: form.value.parent_id,
        description: form.value.description,
        sort_order: form.value.sort_order,
        is_active: form.value.is_active
      }
      if (form.value.delete_image) data.image = null
    }
    
    let response
    if (editingGenre.value) {
      response = await genreApi.updateGenre(editingGenre.value.id, data)
      if (response.data.success) success('Категория обновлена')
    } else {
      response = await genreApi.createGenre(data)
      if (response.data.success) success('Категория создана')
    }
    
    await loadGenres()
    closeModal()
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors
    error('Ошибка при сохранении')
  } finally {
    loading.value = false
  }
}

const toggleActive = async (genre) => {
  try {
    const response = await genreApi.toggleActive(genre.id)
    if (response.data.success) {
      genre.is_active = response.data.is_active
      success(`Категория ${genre.is_active ? 'активирована' : 'деактивирована'}`)
    }
  } catch (err) {
    error('Ошибка при изменении статуса')
  }
}

const deleteGenre = async (genre) => {
  if (confirm(`Удалить категорию "${genre.name}"?`)) {
    try {
      const response = await genreApi.deleteGenre(genre.id)
      if (response.data.success) {
        success('Категория удалена')
        await loadGenres()
      }
    } catch (err) {
      error(err.response?.data?.message || 'Ошибка при удалении')
    }
  }
}

onMounted(() => loadGenres())
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