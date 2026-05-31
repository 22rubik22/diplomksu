<template>
  <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50" @click.self="close">
    <div class="bg-white rounded-2xl p-0 w-full max-w-4xl shadow-2xl transform transition-all animate-modal-slide max-h-[90vh] overflow-y-auto">
      <!-- Заголовок -->
      <div class="sticky top-0 z-10 bg-white border-b border-[#e8e0d8] px-6 py-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
            <i :class="book ? 'fas fa-edit' : 'fas fa-plus'" class="text-[#c8a87c] text-lg"></i>
          </div>
          <div>
            <h3 class="text-xl font-light text-[#2c2c2c]">
              {{ book ? 'Редактировать товар' : 'Добавить товар' }}
            </h3>
            <p class="text-xs text-[#8b7355] mt-0.5">
              {{ book ? 'Измените информацию о товаре' : 'Заполните информацию о новом товаре' }}
            </p>
          </div>
        </div>
        <button @click="close" class="absolute top-5 right-5 text-[#8b7355] hover:text-[#2c2c2c] transition-colors">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>
      
      <form @submit.prevent="saveBook" class="p-6">
        <div class="space-y-6">
          <!-- Изображения товара -->
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-3">Изображения товара</label>
            
            <div class="mb-4">
              <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all">
                <i class="fas fa-upload text-xs"></i>
                Загрузить изображения
                <input 
                  type="file"
                  multiple
                  accept="image/*"
                  class="hidden"
                  @change="handleImagesUpload"
                >
              </label>
              <p class="text-[10px] text-[#8b7355] mt-2">JPG, PNG, GIF, WebP. Максимум 5MB на файл</p>
            </div>
            
            <div v-if="images.length > 0 || existingImages.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="image in existingImages" :key="image.id" class="relative group">
                <div class="relative aspect-square rounded-xl bg-[#faf8f5] border border-[#e8e0d8] overflow-hidden">
                  <img 
                    :src="getFullImageUrl(image.image_path)" 
                    :alt="image.id"
                    class="w-full h-full object-cover"
                  >
                  <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center gap-2">
                    <button 
                      type="button"
                      @click="setPrimaryImage(image.id)"
                      class="w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition flex items-center justify-center"
                      :class="{ 'bg-yellow-600 ring-2 ring-yellow-300': image.is_primary }"
                      title="Сделать основным"
                    >
                      <i class="fas fa-star text-xs"></i>
                    </button>
                    <button 
                      type="button"
                      @click="deleteImage(image.id)"
                      class="w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition flex items-center justify-center"
                      title="Удалить"
                    >
                      <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                  </div>
                </div>
                <div v-if="image.is_primary" class="absolute top-2 left-2 bg-yellow-500 text-white text-[10px] px-2 py-0.5 rounded-full">
                  Основное
                </div>
              </div>
              
              <div v-for="(image, index) in images" :key="index" class="relative group">
                <div class="relative aspect-square rounded-xl bg-[#faf8f5] border border-[#e8e0d8] overflow-hidden">
                  <img :src="image.preview" class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center">
                    <button 
                      type="button"
                      @click="removeNewImage(index)"
                      class="w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition flex items-center justify-center"
                    >
                      <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                  </div>
                </div>
                <div v-if="index === 0 && existingImages.length === 0" class="absolute top-2 left-2 bg-yellow-500 text-white text-[10px] px-2 py-0.5 rounded-full">
                  Будет основным
                </div>
              </div>
            </div>
          </div>
          
          <!-- Основная информация -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">
                Название <span class="text-red-400">*</span>
              </label>
              <input 
                v-model="form.title"
                type="text"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                :class="{ 'border-red-400': errors.title }"
                required
                @input="generateSlug"
              >
              <p v-if="errors.title" class="text-red-400 text-xs mt-1">{{ errors.title[0] }}</p>
            </div>
            
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
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">
                Бренд <span class="text-red-400">*</span>
              </label>
              <div class="flex gap-2">
                <select v-model="form.author_id" class="flex-1 px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer">
                  <option :value="null">Выберите бренд</option>
                  <option v-for="author in authors" :key="author.id" :value="author.id">
                    {{ author.name }}
                  </option>
                </select>
                <button 
                  type="button"
                  @click="openCreateAuthorModal"
                  class="px-3 rounded-xl border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] transition-all"
                >
                  <i class="fas fa-plus text-xs"></i>
                </button>
              </div>
              <p v-if="errors.author_id" class="text-red-400 text-xs mt-1">{{ errors.author_id[0] }}</p>
            </div>
            
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Категории</label>
              <div class="flex gap-2">
                <div class="flex-1">
                  <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2 border border-[#e8e0d8] rounded-xl">
                    <label v-for="genre in genres" :key="genre.id" class="flex items-center gap-2">
                      <input 
                        type="checkbox"
                        :value="genre.id"
                        v-model="form.genre_ids"
                        class="w-3.5 h-3.5 rounded border-[#e8e0d8] text-[#c8a87c] focus:ring-[#c8a87c]"
                      >
                      <span class="text-sm text-[#8b7355]">{{ genre.name }}</span>
                    </label>
                  </div>
                </div>
                <button 
                  type="button"
                  @click="openCreateGenreModal"
                  class="px-3 rounded-xl border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] transition-all"
                >
                  <i class="fas fa-plus text-xs"></i>
                </button>
              </div>
              <div v-if="form.genre_ids && form.genre_ids.length > 0" class="flex flex-wrap gap-1 mt-2">
                <span 
                  v-for="genreId in form.genre_ids" 
                  :key="genreId"
                  class="inline-flex items-center gap-1 px-2 py-0.5 bg-[#faf8f5] text-[#8b7355] rounded-full text-[10px]"
                >
                  {{ getGenreNameById(genreId) }}
                  <button type="button" @click="removeGenre(genreId)" class="hover:text-red-500">×</button>
                </span>
              </div>
            </div>
          </div>
          
          <!-- Характеристики товара -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Цвета</label>
              <div class="relative">
                <input 
                  v-model="colorInput"
                  type="text"
                  class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                  placeholder="Введите цвет и нажмите Enter (например: черный)"
                  @keydown.enter.prevent="addColor"
                >
                <div v-if="form.colors.length > 0" class="flex flex-wrap gap-2 mt-2">
                  <span 
                    v-for="color in form.colors" 
                    :key="color"
                    class="inline-flex items-center gap-1 px-2 py-1 bg-[#faf8f5] text-[#8b7355] rounded-full text-xs"
                  >
                    <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: getColorHex(color) }"></span>
                    {{ color }}
                    <button type="button" @click="removeColor(color)" class="hover:text-red-500">×</button>
                  </span>
                </div>
              </div>
            </div>
            
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Размеры</label>
              <div class="relative">
                <input 
                  v-model="sizeInput"
                  type="text"
                  class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                  placeholder="Введите размер и нажмите Enter (например: S, M, L)"
                  @keydown.enter.prevent="addSize"
                >
                <div v-if="form.sizes.length > 0" class="flex flex-wrap gap-2 mt-2">
                  <span 
                    v-for="size in form.sizes" 
                    :key="size"
                    class="inline-flex items-center gap-1 px-2 py-1 bg-[#faf8f5] text-[#8b7355] rounded-full text-xs"
                  >
                    {{ size }}
                    <button type="button" @click="removeSize(size)" class="hover:text-red-500">×</button>
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Дополнительные характеристики -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Страна производства</label>
              <input 
                v-model="form.country"
                type="text"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                placeholder="Италия"
              >
            </div>
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Состав / Материал</label>
              <input 
                v-model="form.consist"
                type="text"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                placeholder="Натуральная кожа, хлопок"
              >
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Год выпуска коллекции</label>
              <input 
                v-model.number="form.publication_year"
                type="number"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                placeholder="2024"
                min="1900"
                :max="new Date().getFullYear()"
              >
            </div>
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Вес (граммы)</label>
              <input 
                v-model.number="form.weight"
                type="number"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                placeholder="500"
                min="0"
                step="10"
              >
            </div>
          </div>
          
          <!-- Цена и наличие (исправленная логика) -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">
                Основная цена <span class="text-red-400">*</span>
              </label>
              <input 
                v-model.number="form.base_price"
                type="number"
                step="0.01"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                :class="{ 'border-red-400': errors.base_price }"
                required
                min="0"
                @input="updateFinalPrice"
              >
              <p v-if="errors.base_price" class="text-red-400 text-xs mt-1">{{ errors.base_price[0] }}</p>
            </div>
            
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Процент скидки</label>
              <input 
                v-model.number="form.discount_percent"
                type="number"
                step="1"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                placeholder="0"
                min="0"
                max="100"
                @input="updateFinalPrice"
              >
              <p class="text-[10px] text-[#8b7355] mt-1">0-100%</p>
            </div>
            
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">
                Цена со скидкой
              </label>
              <input 
                :value="formattedFinalPrice"
                type="text"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-[#faf8f5] text-[#8b7355] cursor-not-allowed"
                disabled
              >
              <p class="text-[10px] text-[#8b7355] mt-1">Итоговая цена после скидки</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-1 gap-5">
            <div>
              <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">
                Количество <span class="text-red-400">*</span>
              </label>
              <input 
                v-model.number="form.quantity"
                type="number"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
                :class="{ 'border-red-400': errors.quantity }"
                required
                min="0"
              >
              <p v-if="errors.quantity" class="text-red-400 text-xs mt-1">{{ errors.quantity[0] }}</p>
            </div>
          </div>
          
          <!-- Полное описание -->
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Полное описание</label>
            <textarea 
              v-model="form.description"
              rows="5"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all resize-none"
              placeholder="Полное описание товара, особенности, уход..."
            ></textarea>
          </div>
          
          <!-- Статусы -->
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-3">Статусы товара</label>
            <div class="flex flex-wrap gap-4">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" v-model="form.is_active" class="w-4 h-4 rounded border-[#e8e0d8] text-[#c8a87c] focus:ring-[#c8a87c]">
                <span class="text-sm text-[#8b7355]">Активен</span>
              </label>
            </div>
          </div>
          
          <!-- Кнопки действий -->
          <div class="flex gap-3 pt-4 border-t border-[#e8e0d8]">
            <button 
              type="submit"
              class="flex-1 px-6 py-2.5 rounded-xl bg-[#c8a87c] text-white text-sm hover:bg-[#b89a6e] transition-all disabled:opacity-50 flex items-center justify-center gap-2"
              :disabled="loading"
            >
              <i v-if="loading" class="fas fa-spinner fa-spin text-xs"></i>
              <i v-else :class="book ? 'fas fa-save' : 'fas fa-plus'"></i>
              {{ loading ? 'Сохранение...' : (book ? 'Сохранить изменения' : 'Создать товар') }}
            </button>
            <button 
              type="button"
              @click="close"
              class="px-6 py-2.5 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all"
            >
              Отмена
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Модальное окно для создания бренда -->
  <div v-if="showCreateAuthorModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[60]" @click.self="closeCreateAuthorModal">
    <div class="bg-white rounded-2xl p-0 w-full max-w-lg shadow-2xl">
      <div class="border-b border-[#e8e0d8] px-6 py-4">
        <h3 class="text-xl font-light text-[#2c2c2c] flex items-center gap-2">
          <i class="fas fa-user-plus text-[#c8a87c]"></i>
          Добавить бренд
        </h3>
      </div>
      
      <form @submit.prevent="createAuthor" class="p-6">
        <div class="space-y-4">
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">
              Название бренда <span class="text-red-400">*</span>
            </label>
            <input 
              v-model="newAuthor.name"
              type="text"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
              required
              @input="generateAuthorSlug"
            >
          </div>
          
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Slug (URL-идентификатор)</label>
            <div class="relative">
              <input 
                v-model="newAuthor.slug"
                type="text"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all font-mono"
              >
              <button 
                type="button"
                @click="generateAuthorSlug(true)"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8b7355] hover:text-[#c8a87c] text-xs"
              >
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
          </div>
          
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Описание / История бренда</label>
            <textarea 
              v-model="newAuthor.bio"
              rows="3"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all resize-none"
              placeholder="Краткая информация о бренде..."
            ></textarea>
          </div>
          
          <div class="flex gap-3 pt-2">
            <button 
              type="submit"
              class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a87c] text-white text-sm hover:bg-[#b89a6e] transition-all disabled:opacity-50"
              :disabled="creatingAuthor"
            >
              <i v-if="creatingAuthor" class="fas fa-spinner fa-spin mr-2"></i>
              {{ creatingAuthor ? 'Создание...' : 'Создать бренд' }}
            </button>
            <button 
              type="button"
              @click="closeCreateAuthorModal"
              class="px-6 py-2.5 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all"
            >
              Отмена
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Модальное окно для создания категории -->
  <div v-if="showCreateGenreModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[60]" @click.self="closeCreateGenreModal">
    <div class="bg-white rounded-2xl p-0 w-full max-w-lg shadow-2xl">
      <div class="border-b border-[#e8e0d8] px-6 py-4">
        <h3 class="text-xl font-light text-[#2c2c2c] flex items-center gap-2">
          <i class="fas fa-plus-circle text-[#c8a87c]"></i>
          Добавить категорию
        </h3>
      </div>
      
      <form @submit.prevent="createGenre" class="p-6">
        <div class="space-y-4">
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">
              Название категории <span class="text-red-400">*</span>
            </label>
            <input 
              v-model="newGenre.name"
              type="text"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
              required
              @input="generateGenreSlug"
            >
          </div>
          
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Slug (URL-идентификатор)</label>
            <div class="relative">
              <input 
                v-model="newGenre.slug"
                type="text"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all font-mono"
              >
              <button 
                type="button"
                @click="generateGenreSlug(true)"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8b7355] hover:text-[#c8a87c] text-xs"
              >
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
          </div>
          
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Порядок сортировки</label>
            <input 
              v-model.number="newGenre.sort_order"
              type="number"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
              min="0"
            >
          </div>
          
          <div>
            <label class="block text-sm text-[#2c2c2c] font-medium mb-1.5">Описание категории</label>
            <textarea 
              v-model="newGenre.description"
              rows="2"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all resize-none"
              placeholder="Краткое описание категории..."
            ></textarea>
          </div>
          
          <div class="flex gap-3 pt-2">
            <button 
              type="submit"
              class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a87c] text-white text-sm hover:bg-[#b89a6e] transition-all disabled:opacity-50"
              :disabled="creatingGenre"
            >
              <i v-if="creatingGenre" class="fas fa-spinner fa-spin mr-2"></i>
              {{ creatingGenre ? 'Создание...' : 'Создать категорию' }}
            </button>
            <button 
              type="button"
              @click="closeCreateGenreModal"
              class="px-6 py-2.5 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all"
            >
              Отмена
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { bookApi, bookImageApi } from '@/api/books'
import { authorApi } from '@/api/authors'
import { genreApi } from '@/api/genres'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  book: { type: Object, default: null },
  authors: { type: Array, required: true },
  genres: { type: Array, required: true }
})

const emit = defineEmits(['close', 'saved', 'update-authors', 'update-genres'])

const { success, error } = useToast()

const loading = ref(false)
const errors = ref({})
const images = ref([])
const existingImages = ref([])
const fullBookData = ref(null)
const isLoadingBook = ref(false)

// Цвета и размеры
const colorInput = ref('')
const sizeInput = ref('')

const showCreateAuthorModal = ref(false)
const showCreateGenreModal = ref(false)
const creatingAuthor = ref(false)
const creatingGenre = ref(false)

const newAuthor = ref({ name: '', slug: '', bio: '' })
const newGenre = ref({ name: '', slug: '', description: '', sort_order: 0, is_active: true })

// Форма с исправленной логикой цены
const form = ref({
  title: '',
  slug: '',
  author_id: null,
  genre_ids: [],
  colors: [],
  sizes: [],
  country: '',
  consist: '',
  publication_year: null,
  weight: null,
  description: '',
  base_price: null,        // Основная цена (без скидки)
  discount_percent: 0,     // Процент скидки
  final_price: null,       // Итоговая цена (будет рассчитана)
  quantity: 0,
  is_active: true
})

// Форматированная итоговая цена для отображения
const formattedFinalPrice = computed(() => {
  if (form.value.final_price) {
    return new Intl.NumberFormat('ru-RU').format(form.value.final_price) + ' ₽'
  }
  return '0 ₽'
})

// Функция обновления итоговой цены
const updateFinalPrice = () => {
  const basePrice = form.value.base_price || 0
  const discount = form.value.discount_percent || 0
  
  if (discount > 0) {
    const finalPrice = basePrice * (1 - discount / 100)
    form.value.final_price = Math.round(finalPrice * 100) / 100
  } else {
    form.value.final_price = basePrice
  }
}

// Функция для обратного расчета (при редактировании)
const calculateFromFinalPrice = (finalPrice, basePrice) => {
  if (!finalPrice || !basePrice || basePrice <= 0) return 0
  if (finalPrice >= basePrice) return 0
  const discount = ((basePrice - finalPrice) / basePrice) * 100
  return Math.round(discount)
}

// Функция для получения hex кода цвета
const getColorHex = (colorName) => {
  const colors = {
    'черный': '#000000',
    'белый': '#FFFFFF',
    'красный': '#FF0000',
    'синий': '#0000FF',
    'зеленый': '#008000',
    'желтый': '#FFFF00',
    'коричневый': '#8B4513',
    'бежевый': '#F5F5DC',
    'серый': '#808080',
    'розовый': '#FFC0CB',
    'фиолетовый': '#800080',
    'оранжевый': '#FFA500',
    'голубой': '#87CEEB',
    'бордовый': '#800000',
    'хаки': '#C3B091'
  }
  return colors[colorName] || '#c8a87c'
}

const addColor = () => {
  if (colorInput.value.trim() && !form.value.colors.includes(colorInput.value.trim().toLowerCase())) {
    form.value.colors.push(colorInput.value.trim().toLowerCase())
    colorInput.value = ''
  }
}

const removeColor = (color) => {
  const index = form.value.colors.indexOf(color)
  if (index > -1) form.value.colors.splice(index, 1)
}

const addSize = () => {
  if (sizeInput.value.trim() && !form.value.sizes.includes(sizeInput.value.trim().toUpperCase())) {
    form.value.sizes.push(sizeInput.value.trim().toUpperCase())
    sizeInput.value = ''
  }
}

const removeSize = (size) => {
  const index = form.value.sizes.indexOf(size)
  if (index > -1) form.value.sizes.splice(index, 1)
}

const getFullImageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  return `${baseUrl}${path}`
}

const generateSlug = (force = false) => {
  if ((force || !form.value.slug) && form.value.title) {
    form.value.slug = form.value.title
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

const generateAuthorSlug = (force = false) => {
  if ((force || !newAuthor.value.slug) && newAuthor.value.name) {
    newAuthor.value.slug = newAuthor.value.name
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

const generateGenreSlug = (force = false) => {
  if ((force || !newGenre.value.slug) && newGenre.value.name) {
    newGenre.value.slug = newGenre.value.name
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

const loadImages = async () => {
  if (fullBookData.value?.id) {
    try {
      const response = await bookImageApi.getImages(fullBookData.value.id)
      if (response.data.success) existingImages.value = response.data.data
    } catch (err) { console.error('Error loading images:', err) }
  }
}

const initForm = () => {
  if (fullBookData.value) {
    const basePrice = fullBookData.value.old_price || fullBookData.value.price || null
    const finalPrice = fullBookData.value.price || null
    const discountPercent = calculateFromFinalPrice(finalPrice, basePrice)
    
    form.value = {
      title: fullBookData.value.title || '',
      slug: fullBookData.value.slug || '',
      author_id: fullBookData.value.brand?.id || null,
      genre_ids: fullBookData.value.categories?.map(c => c.id) || [],
      colors: fullBookData.value.color_list || [],
      sizes: fullBookData.value.size_list || [],
      country: fullBookData.value.country || '',
      consist: fullBookData.value.consist || '',
      publication_year: fullBookData.value.publication_year || null,
      weight: fullBookData.value.weight || null,
      description: fullBookData.value.description || '',
      base_price: basePrice,
      discount_percent: discountPercent,
      final_price: finalPrice,
      quantity: fullBookData.value.quantity !== undefined ? fullBookData.value.quantity : 0,
      is_active: fullBookData.value.is_active !== undefined ? fullBookData.value.is_active : true
    }
    loadImages()
  }
}

const resetForm = () => {
  form.value = {
    title: '', slug: '', author_id: null, genre_ids: [],
    colors: [], sizes: [],
    country: '', consist: '', publication_year: null, weight: null,
    description: '',
    base_price: null, discount_percent: 0, final_price: null, quantity: 0,
    is_active: true
  }
  existingImages.value = []
  images.value = []
}

const loadFullBookData = async () => {
  if (!props.book?.id) return
  isLoadingBook.value = true
  try {
    const response = await bookApi.getBook(props.book.id)
    if (response.data.success) {
      fullBookData.value = response.data.data
      initForm()
    }
  } catch (err) {
    console.error('Error loading book data:', err)
    error('Ошибка при загрузке данных товара')
  } finally {
    isLoadingBook.value = false
  }
}

watch(() => props.book, (newBook) => {
  if (newBook?.id) loadFullBookData()
  else { fullBookData.value = null; resetForm() }
}, { immediate: true })

const handleImagesUpload = (event) => {
  const files = Array.from(event.target.files)
  files.forEach(file => {
    if (file.size > 5 * 1024 * 1024) return error(`Файл ${file.name} превышает 5MB`)
    const reader = new FileReader()
    reader.onload = (e) => images.value.push({ file, preview: e.target.result })
    reader.readAsDataURL(file)
  })
  event.target.value = ''
}

const removeNewImage = (index) => images.value.splice(index, 1)

const deleteImage = async (imageId) => {
  if (confirm('Вы уверены, что хотите удалить это изображение?')) {
    try {
      await bookImageApi.deleteImage(imageId)
      existingImages.value = existingImages.value.filter(img => img.id !== imageId)
      success('Изображение удалено')
    } catch (err) { error('Ошибка при удалении изображения') }
  }
}

const setPrimaryImage = async (imageId) => {
  try {
    await bookImageApi.setPrimary(imageId)
    existingImages.value.forEach(img => img.is_primary = img.id === imageId)
    success('Основное изображение установлено')
  } catch (err) { error('Ошибка при установке основного изображения') }
}

const saveBook = async () => {
  loading.value = true
  errors.value = {}
  
  try {
    const data = {
      title: form.value.title,
      slug: form.value.slug || null,
      brand_id: form.value.author_id,
      category_ids: form.value.genre_ids,
      color: form.value.colors,
      size: form.value.sizes,
      country: form.value.country || null,
      consist: form.value.consist || null,
      publication_year: form.value.publication_year || null,
      weight: form.value.weight || null,
      description: form.value.description || null,
      price: form.value.final_price,
      old_price: form.value.base_price,
      quantity: form.value.quantity,
      is_active: form.value.is_active
    }
    
    console.log('Отправляемые данные:', data)
    
    let bookId
    if (fullBookData.value?.id) {
      await bookApi.updateBook(fullBookData.value.id, data)
      bookId = fullBookData.value.id
      success('Товар успешно обновлён')
    } else {
      const response = await bookApi.createBook(data)
      bookId = response.data.data.id
      success('Товар успешно создан')
    }
    
    for (let i = 0; i < images.value.length; i++) {
      const fd = new FormData()
      fd.append('image', images.value[i].file)
      fd.append('is_primary', existingImages.value.length === 0 && i === 0 ? '1' : '0')
      fd.append('sort_order', existingImages.value.length + i)
      try { await bookImageApi.uploadImage(bookId, fd) }
      catch (err) { console.error('Error uploading image:', err) }
    }
    emit('saved')
  } catch (err) {
    console.error('Ошибка сохранения:', err.response?.data)
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
      const errorMessages = Object.values(errors.value).flat()
      errorMessages.forEach(msg => error(msg))
    } else if (err.response?.data?.message) {
      error(err.response.data.message)
    } else {
      error('Ошибка при сохранении товара')
    }
  } finally {
    loading.value = false
  }
}

const openCreateAuthorModal = () => { newAuthor.value = { name: '', slug: '', bio: '' }; showCreateAuthorModal.value = true }
const closeCreateAuthorModal = () => showCreateAuthorModal.value = false
const openCreateGenreModal = () => { newGenre.value = { name: '', slug: '', description: '', sort_order: 0, is_active: true }; showCreateGenreModal.value = true }
const closeCreateGenreModal = () => showCreateGenreModal.value = false

const createAuthor = async () => {
  creatingAuthor.value = true
  try {
    const response = await authorApi.createAuthor({ name: newAuthor.value.name, slug: newAuthor.value.slug, bio: newAuthor.value.bio })
    if (response.data.success) {
      success('Бренд успешно создан')
      emit('update-authors')
      form.value.author_id = response.data.data.id
      closeCreateAuthorModal()
    }
  } catch (err) { error('Ошибка при создании бренда') }
  finally { creatingAuthor.value = false }
}

const createGenre = async () => {
  creatingGenre.value = true
  try {
    const response = await genreApi.createGenre({ 
      name: newGenre.value.name, 
      slug: newGenre.value.slug, 
      description: newGenre.value.description, 
      sort_order: newGenre.value.sort_order, 
      is_active: true 
    })
    if (response.data.success) {
      success('Категория успешно создана')
      emit('update-genres')
      if (!form.value.genre_ids.includes(response.data.data.id)) form.value.genre_ids.push(response.data.data.id)
      closeCreateGenreModal()
    }
  } catch (err) { error('Ошибка при создании категории') }
  finally { creatingGenre.value = false }
}

const getGenreNameById = (id) => props.genres.find(g => g.id === id)?.name || ''
const removeGenre = (id) => { const idx = form.value.genre_ids.indexOf(id); if (idx > -1) form.value.genre_ids.splice(idx, 1) }
const close = () => emit('close')
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