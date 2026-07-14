<template>
  <div class="d-flex">
    <SidebarMenu />

    <div class="content-wrapper">
      <div class="content-header">
        <div class="gallery-header">
          <div class="gallery-header__top">
            <h3 class="m-0"><i class="fa-solid fa-images me-2"></i>Galería de fotos</h3>
            <button type="button" class="btn btn-danger btn-sm gallery-create-button" @click="openAlbumModal()" aria-label="Nuevo álbum">
              <i class="fa-solid fa-folder-plus gallery-create-button__icon"></i>
              <span class="gallery-create-button__label">Nuevo álbum</span>
            </button>
          </div>

          <div class="gallery-header__search">
            <input
              v-model="search"
              @input="onSearch"
              type="text"
              class="form-control form-control-sm"
              placeholder="Buscar por nombre del álbum, actividad o descripción..."
            >
          </div>
        </div>
      </div>

      <div class="content">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="gallery-mobile-list">
              <article v-for="album in albums" :key="`mobile-${album.id}`" class="gallery-album-card">
                <div class="gallery-album-card__header">
                  <div class="gallery-album-card__heading">
                    <h4>{{ album.nombre || 'Álbum' }}</h4>
                    <span class="gallery-album-card__badge">{{ album.actividad?.nombre || 'Sin actividad asociada' }}</span>
                  </div>

                  <div class="gallery-album-card__actions">
                    <button type="button" class="btn btn-sm gallery-action-button" title="Editar álbum" aria-label="Editar álbum" @click="openAlbumModal(album)">
                      <i class="fa-solid fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary gallery-open-button" title="Abrir fotos" aria-label="Abrir fotos" @click="openPhotosModal(album)">
                      <i class="fa-solid fa-images"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar álbum" aria-label="Eliminar álbum" :disabled="!canDeleteAlbum(album)" @click="deleteAlbum(album)">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </div>

                <div class="gallery-album-card__details">
                  <div class="gallery-album-card__row">
                    <span class="gallery-album-card__label"><i class="fa-solid fa-id-badge"></i> ID álbum</span>
                    <strong class="gallery-album-card__value">{{ album.id }}</strong>
                  </div>
                  <div class="gallery-album-card__row">
                    <span class="gallery-album-card__label"><i class="fa-solid fa-file-lines"></i> Descripción</span>
                    <strong class="gallery-album-card__value gallery-album-card__value--text">{{ album.descripcion || 'Sin descripción' }}</strong>
                  </div>
                  <div class="gallery-album-card__row">
                    <span class="gallery-album-card__label"><i class="fa-solid fa-user"></i> Creado por</span>
                    <strong class="gallery-album-card__value">{{ album.creador_nombre || album.creador?.name || album.creador?.username || 'Administrador' }}</strong>
                  </div>
                  <div class="gallery-album-card__row gallery-album-card__row--photos">
                    <span class="gallery-album-card__label"><i class="fa-solid fa-images"></i> Fotos</span>
                    <button type="button" class="btn gallery-photo-pill" @click="openPhotosModal(album)">
                      {{ album.fotos_count ?? 0 }} fotos
                    </button>
                  </div>
                </div>
              </article>

              <div v-if="!albums.length" class="gallery-album-card gallery-album-card--empty">
                No hay álbumes disponibles.
              </div>
            </div>

            <div class="table-responsive gallery-table-wrapper">
              <table class="table custom-table custom-table--responsive align-middle">
                <thead>
                  <tr>
                    <th>ID álbum</th>
                    <th>Nombre álbum</th>
                    <th>Actividad asociada</th>
                    <th>Cantidad de fotos</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="album in albums" :key="album.id">
                    <td data-label="ID album">{{ album.id }}</td>
                    <td data-label="Nombre álbum">
                      <div class="fw-semibold">{{ album.nombre }}</div>
                      <small class="text-muted">{{ album.descripcion || 'Sin descripción' }}</small>
                      <small class="text-muted d-block">Creado por {{ album.creador_nombre || album.creador?.name || album.creador?.username || 'Administrador' }}</small>
                    </td>
                    <td data-label="Actividad asociada">{{ album.actividad?.nombre || '-' }}</td>
                    <td data-label="Cantidad de fotos">{{ album.fotos_count ?? 0 }}</td>
                    <td data-label="Acciones">
                      <div class="d-flex justify-content-center gap-2 actions-cell">
                        <button type="button" class="btn btn-sm gallery-action-button" title="Editar álbum" @click="openAlbumModal(album)">
                          <i class="fa-solid fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary gallery-open-button" title="Abrir fotos" @click="openPhotosModal(album)">
                          <i class="fa-solid fa-images"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar álbum" :disabled="!canDeleteAlbum(album)" @click="deleteAlbum(album)">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!albums.length">
                    <td colspan="5" class="text-center py-3 text-muted">No hay álbumes disponibles.</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <nav v-if="meta.last_page > 1" class="mt-3">
              <ul class="pagination cruz-roja-pagination justify-content-end">
                <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                  <button class="page-link" :disabled="meta.current_page === 1" @click="fetchAlbums(meta.current_page - 1)">
                  </button>
                </li>
                <li v-for="page in meta.last_page" :key="page" class="page-item" :class="{ active: meta.current_page === page }">
                  <button class="page-link" @click="fetchAlbums(page)">{{ page }}</button>
                </li>
                <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                  <button class="page-link" :disabled="meta.current_page === meta.last_page" @click="fetchAlbums(meta.current_page + 1)">
                  </button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showAlbumModal" class="gallery-modal">
      <section class="gallery-modal__panel" role="dialog" aria-modal="true">
        <div class="gallery-modal__header">
          <div>
            <span class="modal-badge">Álbum</span>
            <h5>{{ albumForm.id ? 'Editar álbum' : 'Nuevo álbum' }}</h5>
          </div>
          <button type="button" class="btn btn-outline-secondary btn-sm" @click="closeAlbumModal">Cerrar</button>
        </div>

        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <label class="form-label">Nombre álbum</label>
            <input v-model.trim="albumForm.nombre" type="text" class="form-control">
          </div>
          <div class="col-12 col-lg-6">
            <label class="form-label">Actividad asociada</label>
            <select v-model="albumForm.actividad_id" class="form-select">
              <option value="">Selecciona una actividad</option>
              <option v-for="actividad in activities" :key="actividad.id" :value="String(actividad.id)">
                {{ actividad.nombre || 'Actividad sin nombre' }}
              </option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea v-model.trim="albumForm.descripcion" class="form-control" rows="3"></textarea>
          </div>
        </div>

        <div class="gallery-modal__actions">
          <button type="button" class="btn btn-danger" :disabled="savingAlbum" @click="saveAlbum">
            <i class="fa-solid fa-floppy-disk me-2"></i>{{ savingAlbum ? 'Guardando...' : 'Guardar álbum' }}
          </button>
        </div>
      </section>
    </div>

    <div v-if="showPhotosModal" class="gallery-modal">
      <section class="gallery-modal__panel gallery-modal__panel--wide" role="dialog" aria-modal="true">
        <div class="gallery-modal__header">
          <div class="gallery-modal__title-block">
            <span class="modal-badge modal-badge--album">Álbum de {{ selectedAlbum?.actividad?.nombre || 'actividad sin nombre' }}</span>
            <h5>{{ selectedAlbum?.nombre || 'Álbum' }}</h5>
          </div>
          <button type="button" class="btn btn-danger btn-sm modal-exit-button" @click="closePhotosModal">Salir</button>
        </div>

        <div class="photo-upload-panel">
          <input
            ref="albumPhotoInputRef"
            type="file"
            class="visually-hidden"
            accept="image/jpeg,image/jpg,image/png,image/webp"
            multiple
            @change="onAlbumPhotoFilesSelected"
          >
          <button type="button" class="btn btn-outline-primary btn-cruz-roja-outline" @click="openAlbumPhotoPicker">
            <i class="fa-solid fa-folder-open me-2"></i>Seleccionar fotos del dispositivo
          </button>
          <button type="button" class="btn btn-danger" :disabled="uploadingPhotos || !pendingPhotoFiles.length" @click="uploadAlbumPhotos">
            <i class="fa-solid fa-upload me-2"></i>{{ uploadingPhotos ? 'Subiendo...' : 'Subir fotos' }}
          </button>

        </div>

        <div class="album-photo-grid">
          <article v-for="photo in paginatedAlbumPhotos" :key="photo.id" class="album-photo-card">
            <img :src="photo.url_publica" :alt="photo.nombre_original || 'Foto de álbum'" class="album-photo-card__image">
            <div class="album-photo-card__actions album-photo-card__actions--overlay">
              <button type="button" class="btn btn-sm btn-light" title="Descargar foto" aria-label="Descargar foto" @click="downloadAlbumPhoto(photo)">
                <i class="fa-solid fa-download"></i>
              </button>
              <button type="button" class="btn btn-sm btn-light" title="Editar datos de foto" @click="openPhotoEditModal(photo)">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button type="button" class="btn btn-sm btn-danger" title="Eliminar foto" :disabled="!canDeletePhoto(photo)" @click="deletePhoto(photo)">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </article>
          <div v-if="!albumPhotos.length" class="empty-gallery">Este álbum todavía no tiene fotos.</div>
        </div>

        <div v-if="albumPhotoTotalPages > 1" class="photo-pagination" aria-label="Paginación de fotos">
          <button type="button" class="photo-pagination__button" :disabled="albumPhotoPage === 1" aria-label="Página anterior" @click="goToAlbumPhotoPage(albumPhotoPage - 1)">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <span class="photo-pagination__label">{{ albumPhotoPage }} de {{ albumPhotoTotalPages }}</span>
          <button type="button" class="photo-pagination__button" :disabled="albumPhotoPage === albumPhotoTotalPages" aria-label="Página siguiente" @click="goToAlbumPhotoPage(albumPhotoPage + 1)">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>

      </section>
    </div>
    <div v-if="showPhotoEditModal" class="gallery-modal">
      <section class="gallery-modal__panel photo-edit-modal" role="dialog" aria-modal="true">
        <div class="gallery-modal__header">
          <div class="gallery-modal__title-block">
            <span class="modal-badge">Editar foto</span>
            <h5>{{ photoEditForm.nombre || 'Datos de la foto' }}</h5>
          </div>
          <button type="button" class="btn btn-danger btn-sm modal-exit-button" @click="closePhotoEditModal">Salir</button>
        </div>

        <div class="photo-edit-preview" v-if="photoEditForm.url">
          <img :src="photoEditForm.url" :alt="photoEditForm.nombre || 'Foto de álbum'">
        </div>

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Nombre de foto</label>
            <input v-model.trim="photoEditForm.nombre" type="text" class="form-control" placeholder="Nombre de foto">
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea v-model.trim="photoEditForm.descripcion" class="form-control" rows="4" placeholder="Descripción"></textarea>
          </div>
        </div>

        <div class="gallery-modal__actions">
          <button type="button" class="btn btn-outline-secondary" @click="closePhotoEditModal">Cancelar</button>
          <button type="button" class="btn btn-danger" :disabled="savingPhotoEdit" @click="savePhotoEdit">
            <i class="fa-solid fa-floppy-disk me-2"></i>{{ savingPhotoEdit ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </section>
    </div>

  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useStore } from 'vuex'
import axios from 'axios'
import Swal from 'sweetalert2'
import SidebarMenu from '../components/SidebarMenu.vue'
import { buildApiUrl } from '../config/api'
import { optimizeImage } from '../utils/imageOptimization'

const store = useStore()
const currentUser = computed(() => store.getters.authUser)
const isAdministrator = computed(() => store.getters.hasRole('administrador'))
const albums = ref([])
const activities = ref([])
const meta = ref({ current_page: 1, last_page: 1 })
const search = ref('')
const showAlbumModal = ref(false)
const showPhotosModal = ref(false)
const savingAlbum = ref(false)
const uploadingPhotos = ref(false)
const savingPhotoEdit = ref(false)
const selectedAlbum = ref(null)
const albumPhotos = ref([])
const albumPhotoPage = ref(1)
const albumPhotosPerPage = 12
const pendingPhotoFiles = ref([])
const albumPhotoInputRef = ref(null)
const editingPhoto = ref(null)

const albumForm = reactive({
  id: null,
  nombre: '',
  descripcion: '',
  actividad_id: ''
})

const photoEditForm = reactive({
  id: null,
  nombre: '',
  descripcion: '',
  url: ''
})

const showPhotoEditModal = computed(() => Boolean(editingPhoto.value))

const albumPhotoTotalPages = computed(() => Math.max(1, Math.ceil(albumPhotos.value.length / albumPhotosPerPage)))
const paginatedAlbumPhotos = computed(() => {
  const start = (albumPhotoPage.value - 1) * albumPhotosPerPage

  return albumPhotos.value.slice(start, start + albumPhotosPerPage)
})

async function fetchAlbums(page = 1) {
  try {
    const response = await axios.get(buildApiUrl('albumes'), {
      params: {
        page,
        search: search.value || undefined
      }
    })

    albums.value = response.data.data || []
    meta.value = {
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1
    }
  } catch (error) {
    albums.value = []
    Swal.fire('Error', 'No se pudieron cargar los álbumes.', 'error')
  }
}

function onSearch() {
  fetchAlbums(1)
}

async function fetchActivities() {
  const collected = []
  let currentPage = 1
  let lastPage = 1

  try {
    do {
      const response = await axios.get(buildApiUrl('actividad'), { params: { page: currentPage } })
      collected.push(...(response.data.data || []))
      lastPage = Number(response.data.last_page || currentPage)
      currentPage += 1
    } while (currentPage <= lastPage)

    activities.value = collected
  } catch (error) {
    activities.value = []
  }
}

function resetAlbumForm() {
  albumForm.id = null
  albumForm.nombre = ''
  albumForm.descripcion = ''
  albumForm.actividad_id = ''
}

function openAlbumModal(album = null) {
  resetAlbumForm()

  if (album) {
    albumForm.id = album.id
    albumForm.nombre = album.nombre || ''
    albumForm.descripcion = album.descripcion || ''
    albumForm.actividad_id = album.actividad_id ? String(album.actividad_id) : String(album.actividad?.id || '')
  }

  showAlbumModal.value = true
}

function closeAlbumModal() {
  showAlbumModal.value = false
}

async function saveAlbum() {
  if (!albumForm.nombre || !albumForm.actividad_id) {
    Swal.fire('Faltan datos', 'Completa el nombre del álbum y selecciona una actividad.', 'warning')
    return
  }

  savingAlbum.value = true
  const payload = {
    nombre: albumForm.nombre,
    descripcion: albumForm.descripcion || null,
    actividad_id: Number(albumForm.actividad_id),
    creado_por: isAdministrator.value ? null : (currentUser.value?.id || null)
  }

  try {
    if (albumForm.id) {
      await axios.put(buildApiUrl('albumes/' + albumForm.id), payload)
    } else {
      await axios.post(buildApiUrl('albumes'), payload)
    }

    closeAlbumModal()
    await fetchAlbums(meta.value.current_page)
    Swal.fire('Guardado', 'El álbum fue guardado correctamente.', 'success')
  } catch (error) {
    Swal.fire('Error', error?.response?.data?.message || 'No se pudo guardar el álbum.', 'error')
  } finally {
    savingAlbum.value = false
  }
}

async function openPhotosModal(album) {
  try {
    const response = await axios.get(buildApiUrl('albumes/' + album.id))
    selectedAlbum.value = response.data
    albumPhotos.value = response.data.fotos || []
    albumPhotoPage.value = 1
    pendingPhotoFiles.value = []
    showPhotosModal.value = true
  } catch (error) {
    Swal.fire('Error', 'No se pudo abrir el álbum.', 'error')
  }
}

function closePhotosModal() {
  showPhotosModal.value = false
  selectedAlbum.value = null
  albumPhotos.value = []
  albumPhotoPage.value = 1
  pendingPhotoFiles.value = []
}

function openAlbumPhotoPicker() {
  albumPhotoInputRef.value?.click()
}

function onAlbumPhotoFilesSelected(event) {
  pendingPhotoFiles.value = Array.from(event.target.files || [])
}

async function uploadAlbumPhotos() {
  if (!selectedAlbum.value || !pendingPhotoFiles.value.length) {
    return
  }

  uploadingPhotos.value = true

  try {
    for (const file of pendingPhotoFiles.value) {
      const optimizedFile = await optimizeImage(file)
      const formData = new FormData()
      formData.append('archivo', optimizedFile)
      formData.append('nombre', file.name.replace(/\.[^.]+$/, ''))
      if (!isAdministrator.value && currentUser.value?.id) {
        formData.append('subido_por', String(currentUser.value.id))
      }

      const response = await axios.post(buildApiUrl('albumes/' + selectedAlbum.value.id + '/fotos'), formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      albumPhotos.value.unshift(response.data)
    }

    pendingPhotoFiles.value = []
    if (albumPhotoInputRef.value) {
      albumPhotoInputRef.value.value = ''
    }
    albumPhotoPage.value = 1
    await fetchAlbums(meta.value.current_page)
    Swal.fire('Fotos cargadas', 'Las fotos fueron subidas y asociadas a la actividad.', 'success')
  } catch (error) {
    Swal.fire('Error', error?.response?.data?.message || 'No se pudieron subir las fotos.', 'error')
  } finally {
    uploadingPhotos.value = false
  }
}

function openPhotoEditModal(photo) {
  editingPhoto.value = photo
  photoEditForm.id = photo.id
  photoEditForm.nombre = photo.nombre_original || ''
  photoEditForm.descripcion = photo.descripcion || ''
  photoEditForm.url = photo.url_publica || ''
}

function closePhotoEditModal() {
  editingPhoto.value = null
  photoEditForm.id = null
  photoEditForm.nombre = ''
  photoEditForm.descripcion = ''
  photoEditForm.url = ''
  savingPhotoEdit.value = false
}

async function savePhotoEdit() {
  if (!editingPhoto.value) {
    return
  }

  savingPhotoEdit.value = true

  try {
    editingPhoto.value.nombre_original = photoEditForm.nombre
    editingPhoto.value.descripcion = photoEditForm.descripcion
    await persistPhoto(editingPhoto.value)
    closePhotoEditModal()
    Swal.fire({
      title: 'Actualizada',
      text: 'La foto fue actualizada correctamente.',
      icon: 'success',
      customClass: { container: 'swal-over-gallery-modal' }
    })
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: 'No se pudo actualizar la foto.',
      icon: 'error',
      customClass: { container: 'swal-over-gallery-modal' }
    })
  } finally {
    savingPhotoEdit.value = false
  }
}
async function persistPhoto(photo) {
  const response = await axios.put(buildApiUrl('albumes/' + selectedAlbum.value.id + '/fotos/' + photo.id), {
    nombre: photo.nombre_original || null,
    descripcion: photo.descripcion || null
  })

  const index = albumPhotos.value.findIndex((item) => item.id === photo.id)
  if (index >= 0) {
    albumPhotos.value.splice(index, 1, response.data)
  }

  return response.data
}

async function downloadAlbumPhoto(photo) {
  if (!selectedAlbum.value) return

  try {
    const response = await axios.get(
      buildApiUrl(`albumes/${selectedAlbum.value.id}/fotos/${photo.id}/descargar`),
      { responseType: 'blob' }
    )
    const objectUrl = URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = objectUrl
    const baseName = photo.nombre_original || `fotografia-${photo.id}`
    link.download = /\.[a-z0-9]+$/i.test(baseName) ? baseName : `${baseName}.${photo.extension || 'webp'}`
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(objectUrl)
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error?.response?.status === 403
        ? 'No tienes permisos para descargar esta fotografía.'
        : 'No se pudo descargar la fotografía.',
      icon: 'error',
      customClass: { container: 'swal-over-gallery-modal' }
    })
  }
}

async function updatePhoto(photo) {
  try {
    await persistPhoto(photo)
    Swal.fire({
      title: 'Actualizada',
      text: 'La foto fue actualizada correctamente.',
      icon: 'success',
      customClass: { container: 'swal-over-gallery-modal' }
    })
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: 'No se pudo actualizar la foto.',
      icon: 'error',
      customClass: { container: 'swal-over-gallery-modal' }
    })
  }
}

function getActorId() {
  return Number(currentUser.value?.id || currentUser.value?.user_id || currentUser.value?.usuario_id || 0)
}

function actorDeleteConfig() {
  const actorId = getActorId()

  return {
    params: actorId ? { actor_id: actorId } : {},
    data: actorId ? { actor_id: actorId } : {}
  }
}

async function deletePhoto(photo) {
  if (!canDeletePhoto(photo)) {
    return
  }

  if (!getActorId()) {
    Swal.fire('Sesión requerida', 'Vuelve a iniciar sesión para confirmar tus permisos antes de eliminar fotos.', 'warning')
    return
  }

  const result = await Swal.fire({
    title: 'Eliminar foto',
    text: 'Esta acción quitará la foto del álbum y de la galería asociada a la actividad.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (!result.isConfirmed) {
    return
  }

  try {
    await axios.delete(buildApiUrl('albumes/' + selectedAlbum.value.id + '/fotos/' + photo.id), actorDeleteConfig())
    albumPhotos.value = albumPhotos.value.filter((item) => item.id !== photo.id)
    if (albumPhotoPage.value > albumPhotoTotalPages.value) {
      albumPhotoPage.value = albumPhotoTotalPages.value
    }
    await fetchAlbums(meta.value.current_page)
  } catch (error) {
    Swal.fire('Error', error?.response?.data?.message || 'No se pudo eliminar la foto.', 'error')
  }
}

async function deleteAlbum(album) {
  if (!canDeleteAlbum(album)) {
    return
  }

  if (!getActorId()) {
    Swal.fire('Sesión requerida', 'Vuelve a iniciar sesión para confirmar tus permisos antes de eliminar álbumes.', 'warning')
    return
  }

  const result = await Swal.fire({
    title: 'Eliminar álbum',
    text: 'Se eliminarán también sus fotos asociadas.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (!result.isConfirmed) {
    return
  }

  try {
    await axios.delete(buildApiUrl('albumes/' + album.id), actorDeleteConfig())
    await fetchAlbums(meta.value.current_page)
  } catch (error) {
    Swal.fire('Error', error?.response?.data?.message || 'No se pudo eliminar el álbum.', 'error')
  }
}

function goToAlbumPhotoPage(page) {
  albumPhotoPage.value = Math.min(Math.max(1, page), albumPhotoTotalPages.value)
}
function canDeletePhoto(photo) {
  return isAdministrator.value || Number(photo.subido_por?.id || photo.subido_por) === Number(currentUser.value?.id)
}

function canDeleteAlbum(album) {
  return isAdministrator.value || Number(album.creado_por?.id || album.creado_por) === Number(currentUser.value?.id)
}

onMounted(async () => {
  await Promise.all([fetchAlbums(), fetchActivities()])
})
</script>

<style scoped>
.content-wrapper {
  flex: 1;
  background-color: #f5f7fa;
  min-height: 100vh;
}

.content-header {
  padding: 1rem 1.5rem;
  background-color: #fff;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 1.5rem;
}

.content {
  padding: 0 1.5rem 1.5rem;
}

.gallery-header {
  display: grid;
  gap: 0.9rem;
}

.gallery-header__top {
  display: flex;
  align-items: stretch;
  justify-content: space-between;
  gap: 0.75rem;
}

.gallery-header__top h3 {
  display: flex;
  align-items: center;
  min-height: 56px;
  font-size: 2rem;
  line-height: 1.05;
}

.gallery-header__search {
  display: flex;
  width: min(100%, 28rem);
}

.gallery-header__search .form-control {
  min-height: 52px;
  border-radius: 16px;
  font-size: 1rem;
  padding-inline: 0.95rem;
  box-shadow: none;
}

.gallery-create-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  min-width: 44px;
  min-height: 56px;
  padding: 0.85rem 1.2rem;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.1;
  white-space: nowrap;
}

.gallery-create-button__icon {
  font-size: 1rem;
}

.gallery-mobile-list {
  display: none;
}

.gallery-table-wrapper {
  display: block;
}

.gallery-action-button {
  border-color: #0f4c81;
  color: #0f4c81;
  border-radius: 12px;
}

.gallery-action-button:hover,
.gallery-action-button:focus,
.gallery-action-button:active {
  border-color: #0c416d;
  color: #0c416d;
  background: #edf5fb;
}

.gallery-photo-pill {
  min-height: 40px;
  padding: 0.72rem 1.35rem;
  border-radius: 999px;
  border: 1px solid #e23745;
  background: #e23745;
  color: #fff;
  font-size: 1rem;
  font-weight: 800;
  line-height: 1;
  box-shadow: none;
}

.gallery-photo-pill:hover,
.gallery-photo-pill:focus,
.gallery-photo-pill:active {
  border-color: #c92b39;
  background: #c92b39;
  color: #fff;
}

.custom-table th {
  background-color: #f8f9fa;
  color: #495057;
  font-weight: 600;
}

.actions-cell .btn {
  min-width: 34px;
}

.gallery-modal {
  position: fixed;
  inset: 0;
  z-index: 2000;
  background: rgba(15, 23, 42, 0.48);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.gallery-modal__panel {
  width: min(720px, 100%);
  max-height: 92vh;
  overflow: auto;
  background: #fff;
  border-radius: 8px;
  padding: 1.25rem;
  box-shadow: 0 24px 60px rgba(15, 29, 51, 0.24);
}

.gallery-modal__panel--wide {
  width: min(1040px, 100%);
}

.gallery-modal__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.65rem;
}

.gallery-modal__title-block h5 {
  color: #0f2f5f;
  font-size: 1.65rem;
  font-weight: 800;
  margin: 0.25rem 0 0;
}

.modal-exit-button {
  min-width: 86px;
  font-weight: 700;
  background: #e01e1e;
  border-color: #e01e1e;
}

.modal-badge {
  display: flex;
  font-size: 0.72rem;
  font-weight: 700;
  color: #173b70;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.modal-badge--album {
  color: #0f2f5f;
  font-size: 1.44rem;
  line-height: 1.15;
  text-transform: none;
}

.gallery-modal__actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1rem;
}

.photo-upload-panel {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
  padding: 0.75rem 0.9rem;
  border: 1px solid #c8d8eb;
  border-radius: 8px;
  margin-bottom: 0.8rem;
}

.btn-cruz-roja-outline {
  color: #0f2f5f;
  border-color: #0f2f5f;
  font-weight: 700;
}

.btn-cruz-roja-outline:hover,
.btn-cruz-roja-outline:focus {
  color: #fff;
  background: #0f2f5f;
  border-color: #0f2f5f;
}

.gallery-open-button i {
  color: #0f2f5f;
}

.gallery-open-button:hover i,
.gallery-open-button:focus-visible i {
  color: #fff;
}

.album-photo-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  grid-template-rows: repeat(3, minmax(0, auto));
  gap: 0.55rem;
  min-height: 0;
  align-content: start;
}

.album-photo-card {
  position: relative;
  border: 1px solid #e3eaf3;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
}

.album-photo-card__image {
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
  background: #f2f6fb;
}

.album-photo-card__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.album-photo-card__actions--overlay {
  position: absolute;
  right: 0.4rem;
  bottom: 0.4rem;
  padding: 0.25rem;
  border-radius: 999px;
  background: transparent;
  backdrop-filter: none;
}

.album-photo-card__actions--overlay .btn {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.photo-edit-modal {
  width: min(620px, 100%);
}

.photo-edit-preview {
  margin-bottom: 1rem;
  border-radius: 8px;
  overflow: hidden;
  background: #f2f6fb;
}

.photo-edit-preview img {
  width: 100%;
  max-height: 280px;
  object-fit: cover;
  display: block;
}

.photo-pagination {
  width: max-content;
  margin: 0.65rem 0 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  background: #fff;
  box-shadow: 0 8px 18px rgba(15, 47, 95, 0.12);
}

.photo-pagination__button {
  width: 42px;
  height: 36px;
  border: 0;
  background: #fff;
  color: #e01e1e;
  display: flex;
  align-items: center;
  justify-content: center;
}

.photo-pagination__button:disabled {
  color: #cbd5e1;
  cursor: not-allowed;
}

.photo-pagination__label {
  min-width: 82px;
  padding: 0 0.75rem;
  border-left: 1px solid #e2e8f0;
  border-right: 1px solid #e2e8f0;
  color: #1f2937;
  font-size: 0.78rem;
  font-weight: 800;
  line-height: 36px;
  text-align: center;
  text-transform: uppercase;
}

.empty-gallery {
  grid-column: 1 / -1;
  color: #6c757d;
  text-align: center;
  padding: 2rem 1rem;
  border: 1px dashed #d2dfed;
  border-radius: 8px;
}

@media (max-width: 768px) {
  .gallery-header__top {
    align-items: center;
  }

  .gallery-header__top h3 {
    min-height: 44px;
    font-size: 1.75rem;
  }

  .gallery-header__search {
    width: 100%;
  }

  .gallery-header__search .form-control {
    min-height: 46px;
    border-radius: 14px;
    font-size: 0.98rem;
  }

  .gallery-create-button {
    width: 44px;
    min-width: 44px;
    height: 44px;
    min-height: 44px;
    padding: 0;
    border-radius: 12px;
    flex-shrink: 0;
  }

  .gallery-create-button__label {
    display: none;
  }

  .album-photo-grid {
    grid-template-columns: 1fr;
    min-height: auto;
  }

  .modal-badge--album {
    font-size: 1.1rem;
  }

  .gallery-modal__title-block h5 {
    font-size: 1.25rem;
  }
}

@media (max-width: 575.98px) {
  .content-header {
    padding: 0.9rem 1rem;
  }

  .content {
    padding: 0 1rem 1rem;
  }

  .gallery-create-button {
    width: auto;
    min-width: 3rem;
    padding-inline: 0.9rem;
    justify-content: center;
  }

  .gallery-create-button__icon {
    font-size: 1.35rem;
  }

  .gallery-create-button__label {
    display: none;
  }

  .gallery-mobile-list {
    display: grid;
    gap: 0.95rem;
  }

  .gallery-table-wrapper {
    display: none;
  }

  .gallery-album-card {
    border: 1px solid #e5eaf1;
    border-radius: 22px;
    padding: 1.1rem 1rem 1rem;
    background: #fff;
    box-shadow: 0 16px 30px rgba(15, 47, 95, 0.08);
  }

  .gallery-album-card--empty {
    text-align: center;
    color: #64748b;
    font-weight: 600;
  }

  .gallery-album-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.85rem;
    margin-bottom: 0.95rem;
  }

  .gallery-album-card__heading {
    min-width: 0;
  }

  .gallery-album-card__heading h4 {
    margin: 0 0 0.7rem;
    color: #12284c;
    font-size: 1.45rem;
    line-height: 1.1;
    font-weight: 800;
  }

  .gallery-album-card__badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 0.45rem 0.95rem;
    border: 1.5px solid #0f4c81;
    border-radius: 999px;
    color: #0f4c81;
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1.1;
    max-width: 100%;
  }

  .gallery-album-card__actions {
    display: flex;
    gap: 0.45rem;
    flex-shrink: 0;
  }

  .gallery-album-card__actions .btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
  }

  .gallery-album-card__actions .btn i {
    font-size: 1.1rem;
  }

  .gallery-album-card__details {
    display: grid;
  }

  .gallery-album-card__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    padding: 0.78rem 0;
    border-top: 1px solid #e6edf5;
  }

  .gallery-album-card__row:first-child {
    border-top: none;
    padding-top: 0;
  }

  .gallery-album-card__label {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    color: #244169;
    font-size: 0.95rem;
    line-height: 1.25;
    min-width: 8rem;
  }

  .gallery-album-card__label i {
    width: 1.15rem;
    color: #e01e1e;
    font-size: 1.05rem;
    text-align: center;
  }

  .gallery-album-card__value {
    color: #12284c;
    font-size: 1.05rem;
    line-height: 1.3;
    text-align: right;
  }

  .gallery-album-card__value--text {
    max-width: 52%;
  }

  .gallery-album-card__row--photos {
    align-items: center;
  }
}
</style>





















