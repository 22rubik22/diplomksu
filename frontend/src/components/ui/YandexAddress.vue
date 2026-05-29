<template>
    <div class="address-selector">
      <!-- Поле ввода с подсказками -->
      <div class="relative">
        <label class="block text-[#5e1104] mb-2">Адрес доставки *</label>
        <input
          type="text"
          v-model="searchQuery"
          @input="onSearchInput"
          @focus="showSuggestions = true"
          @blur="handleBlur"
          placeholder="Начните вводить адрес, город или метро"
          class="form-input w-full"
          :class="{ 'border-red-500': error }"
        />
        
        <!-- Список подсказок -->
        <div 
          v-if="showSuggestions && suggestions.length > 0"
          class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
        >
          <div
            v-for="(suggestion, index) in suggestions"
            :key="index"
            @click="selectSuggestion(suggestion)"
            class="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0"
          >
            <div class="text-sm" v-html="highlightText(suggestion.value)"></div>
            <div class="text-xs text-gray-500 mt-1">{{ suggestion.subtitle }}</div>
          </div>
        </div>
      </div>
      
      <!-- Кнопка выбора на карте -->
      <button
        type="button"
        @click="openMapModal"
        class="mt-2 text-sm text-[#7f8330] hover:text-[#5e1104] transition-colors"
      >
        <i class="fas fa-map-marker-alt mr-1"></i>
        Выбрать на карте
      </button>
      
      <!-- Отображение выбранных координат -->
      <div v-if="selectedCoordinates" class="mt-2 text-xs text-gray-500">
        <i class="fas fa-location-dot mr-1"></i>
        Координаты выбраны
      </div>
      
      <div v-if="error" class="mt-1 text-xs text-red-500">
        {{ error }}
      </div>
  
      <!-- Модальное окно с картой -->
      <div 
        v-if="showMapModal" 
        class="fixed inset-0 bg-black/70 z-[9999] flex items-center justify-center p-4"
        @click.self="closeMapModal"
      >
        <div class="bg-white rounded-xl w-full max-w-4xl h-[80vh] flex flex-col relative z-[10000]">
          <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold text-[#5e1104]">Выберите адрес на карте</h3>
            <button @click="closeMapModal" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
          
          <div id="map" class="flex-1" style="min-height: 400px;"></div>
          
          <div class="p-4 border-t">
            <div class="mb-3">
              <p class="text-sm text-gray-600">
                <i class="fas fa-location-dot mr-1 text-[#7f8330]"></i>
                Выбранный адрес: <span class="font-medium">{{ selectedAddress || 'Не выбран' }}</span>
              </p>
            </div>
            <div class="flex gap-3">
              <button
                @click="confirmAddress"
                class="flex-1 bg-[#7f8330] text-white py-2 rounded-lg hover:bg-[#5e1104] transition-colors"
                :disabled="!selectedAddress"
              >
                Подтвердить адрес
              </button>
              <button
                @click="closeMapModal"
                class="flex-1 border border-gray-300 py-2 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Отмена
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, watch, onUnmounted, onMounted } from 'vue'
  
  // API ключ Яндекс Карт (закодированный для разработки)
  const YANDEX_MAPS_API_KEY = '5c522b98-7dfa-42ca-8c74-bcd2aab7f92c'
  
  const props = defineProps({
    modelValue: {
      type: String,
      default: ''
    },
    error: {
      type: String,
      default: ''
    }
  })
  
  const emit = defineEmits(['update:modelValue', 'coordinates'])
  
  const searchQuery = ref(props.modelValue)
  const showSuggestions = ref(false)
  const suggestions = ref([])
  const showMapModal = ref(false)
  let map = null
  let placemark = null
  let ymapsReady = false
  
  const selectedAddress = ref('')
  const selectedCoordinates = ref(null)
  
  // Обработчик для blur с window.setTimeout
  const handleBlur = () => {
    window.setTimeout(() => {
      showSuggestions.value = false
    }, 200)
  }
  
  // Ждем загрузки Яндекс Карт
  const waitForYmaps = () => {
    return new Promise((resolve) => {
      if (typeof window.ymaps !== 'undefined') {
        window.ymaps.ready(() => {
          ymapsReady = true
          resolve()
        })
      } else {
        const checkInterval = window.setInterval(() => {
          if (typeof window.ymaps !== 'undefined') {
            window.clearInterval(checkInterval)
            window.ymaps.ready(() => {
              ymapsReady = true
              resolve()
            })
          }
        }, 100)
      }
    })
  }
  
  // Следим за изменением modelValue извне
  watch(() => props.modelValue, (newValue) => {
    if (newValue !== searchQuery.value) {
      searchQuery.value = newValue
    }
  })
  
  // Поиск подсказок адресов
  const onSearchInput = async () => {
    if (searchQuery.value.length < 3) {
      suggestions.value = []
      return
    }
    
    if (!YANDEX_MAPS_API_KEY) {
      console.warn('API ключ не настроен')
      return
    }
    
    try {
      const response = await fetch(
        `https://geocode-maps.yandex.ru/1.x/?apikey=${YANDEX_MAPS_API_KEY}&geocode=${encodeURIComponent(searchQuery.value)}&format=json&results=10`
      )
      const data = await response.json()
      
      if (!data.response || !data.response.GeoObjectCollection) {
        console.warn('Некорректный ответ от API')
        return
      }
      
      const geoObjects = data.response.GeoObjectCollection.featureMember || []
      
      suggestions.value = geoObjects.map(item => {
        const geoObject = item.GeoObject
        const address = geoObject.metaDataProperty.GeocoderMetaData.text
        const coords = geoObject.Point.pos.split(' ')
        
        return {
          value: address,
          subtitle: geoObject.description || 'Адрес',
          coordinates: {
            lat: parseFloat(coords[1]),
            lon: parseFloat(coords[0])
          }
        }
      })
      
      showSuggestions.value = true
    } catch (error) {
      console.error('Ошибка при получении подсказок:', error)
      suggestions.value = []
    }
  }
  
  // Выбор подсказки
  const selectSuggestion = (suggestion) => {
    searchQuery.value = suggestion.value
    selectedAddress.value = suggestion.value
    selectedCoordinates.value = suggestion.coordinates
    emit('update:modelValue', suggestion.value)
    emit('coordinates', suggestion.coordinates)
    showSuggestions.value = false
  }
  
  // Выделение текста в подсказке
  const highlightText = (text) => {
    const query = searchQuery.value
    if (!query) return text
    
    const regex = new RegExp(`(${query})`, 'gi')
    return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>')
  }
  
  // Открыть модальное окно с картой
  const openMapModal = async () => {
    showMapModal.value = true
    await waitForYmaps()
    window.setTimeout(() => {
      initMap()
    }, 200)
  }
  
  // Закрыть модальное окно
  const closeMapModal = () => {
    showMapModal.value = false
    if (map) {
      map.destroy()
      map = null
    }
    placemark = null
  }
  
  // Инициализация карты
  const initMap = () => {
    if (!ymapsReady || !window.ymaps) {
      console.error('Яндекс Карты не загружены')
      return
    }
    
    // Проверяем, существует ли элемент map
    const mapElement = document.getElementById('map')
    if (!mapElement) {
      console.error('Элемент карты не найден')
      return
    }
    
    try {
      const defaultCenter = selectedCoordinates.value 
        ? [selectedCoordinates.value.lat, selectedCoordinates.value.lon]
        : [52.286387, 104.280655] // Иркутск по умолчанию
      
      map = new window.ymaps.Map('map', {
        center: defaultCenter,
        zoom: 12,
        controls: ['zoomControl', 'fullscreenControl']
      })
      
      // Добавляем поиск по карте
      const searchControl = new window.ymaps.control.SearchControl({
        options: {
          provider: 'yandex#search',
          noPlacemark: false,
          resultsPerPage: 5
        }
      })
      map.controls.add(searchControl)
      
      // Создаем метку, если есть координаты
      if (selectedCoordinates.value) {
        placemark = new window.ymaps.Placemark(
          [selectedCoordinates.value.lat, selectedCoordinates.value.lon],
          {
            hintContent: 'Выбранный адрес',
            balloonContent: selectedAddress.value
          },
          {
            preset: 'islands#redDotIcon',
            draggable: true
          }
        )
        map.geoObjects.add(placemark)
        
        // Обработчик перетаскивания метки
        placemark.events.add('dragend', () => {
          const coords = placemark.geometry.getCoordinates()
          selectedCoordinates.value = { lat: coords[0], lon: coords[1] }
          reverseGeocode(selectedCoordinates.value)
        })
      }
      
      // Обработчик клика по карте
      map.events.add('click', (e) => {
        const coords = e.get('coords')
        
        if (placemark) {
          placemark.geometry.setCoordinates(coords)
        } else {
          placemark = new window.ymaps.Placemark(
            coords,
            {},
            { preset: 'islands#redDotIcon', draggable: true }
          )
          map.geoObjects.add(placemark)
          
          placemark.events.add('dragend', () => {
            const newCoords = placemark.geometry.getCoordinates()
            selectedCoordinates.value = { lat: newCoords[0], lon: newCoords[1] }
            reverseGeocode(selectedCoordinates.value)
          })
        }
        
        selectedCoordinates.value = { lat: coords[0], lon: coords[1] }
        reverseGeocode(selectedCoordinates.value)
      })
    } catch (error) {
      console.error('Ошибка инициализации карты:', error)
    }
  }
  
  // Обратное геокодирование (получение адреса по координатам)
  const reverseGeocode = async (coords) => {
    if (!YANDEX_MAPS_API_KEY) return
    
    try {
      const response = await fetch(
        `https://geocode-maps.yandex.ru/1.x/?apikey=${YANDEX_MAPS_API_KEY}&geocode=${coords.lon},${coords.lat}&format=json&results=1`
      )
      const data = await response.json()
      
      const geoObjects = data.response?.GeoObjectCollection?.featureMember
      if (geoObjects && geoObjects.length > 0) {
        const geoObject = geoObjects[0].GeoObject
        const address = geoObject.metaDataProperty.GeocoderMetaData.text
        selectedAddress.value = address
        searchQuery.value = address
        emit('update:modelValue', address)
      }
    } catch (error) {
      console.error('Ошибка обратного геокодирования:', error)
    }
  }
  
  // Подтверждение выбранного адреса
  const confirmAddress = () => {
    if (selectedAddress.value) {
      searchQuery.value = selectedAddress.value
      emit('update:modelValue', selectedAddress.value)
      emit('coordinates', selectedCoordinates.value)
      closeMapModal()
    }
  }
  
  onMounted(() => {
    console.log('API ключ настроен:', YANDEX_MAPS_API_KEY ? 'Да' : 'Нет')
    
    // Проверяем, загрузились ли Яндекс Карты
    if (typeof window.ymaps !== 'undefined') {
      console.log('Яндекс Карты загружены')
      window.ymaps.ready(() => {
        ymapsReady = true
        console.log('Яндекс Карты готовы')
      })
    } else {
      console.warn('Скрипт Яндекс Карт не найден в окне')
    }
  })
  
  // Очистка при размонтировании
  onUnmounted(() => {
    if (map) {
      map.destroy()
    }
  })
  </script>
  
  <style scoped>
  .form-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    transition: all 0.2s;
  }
  
  .form-input:focus {
    outline: none;
    border-color: #7f8330;
    box-shadow: 0 0 0 3px rgba(127, 131, 48, 0.1);
  }
  
  .form-input.border-red-500 {
    border-color: #ef4444;
  }
  </style>