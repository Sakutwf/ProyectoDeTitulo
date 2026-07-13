const OPTIMIZABLE_IMAGE_TYPES = new Set(['image/jpeg', 'image/png', 'image/webp'])

function optimizedFileName(name, mimeType) {
  const extension = mimeType === 'image/webp' ? 'webp' : mimeType === 'image/png' ? 'png' : 'jpg'
  return `${String(name || 'imagen').replace(/\.[^.]+$/, '')}.${extension}`
}

function canvasToBlob(canvas, type, quality) {
  return new Promise((resolve) => canvas.toBlob(resolve, type, quality))
}

async function decodeImage(file) {
  if (typeof createImageBitmap === 'function') {
    try {
      return await createImageBitmap(file, { imageOrientation: 'from-image' })
    } catch {
      // Algunos navegadores no aceptan imageOrientation; se usa el fallback inferior.
    }
  }

  const objectUrl = URL.createObjectURL(file)
  try {
    const image = new Image()
    image.decoding = 'async'
    image.src = objectUrl
    await image.decode()
    return image
  } finally {
    URL.revokeObjectURL(objectUrl)
  }
}

/**
 * Reduce el peso de una imagen antes de subirla. Mantiene la proporción,
 * limita solamente fotografías sobredimensionadas, elimina metadatos al
 * dibujar en canvas y genera WebP con calidad visual alta.
 */
export async function optimizeImage(file, options = {}) {
  if (!(file instanceof File) || !OPTIMIZABLE_IMAGE_TYPES.has(file.type)) {
    return file
  }

  const {
    maxWidth = 1920,
    maxHeight = 1920,
    quality = 0.82,
    maxInputBytes = 10 * 1024 * 1024,
    maxOutputBytes = 5 * 1024 * 1024
  } = options

  if (file.size > maxInputBytes) {
    throw new Error('La imagen supera el tamaño máximo permitido antes de optimizarla.')
  }

  let source
  try {
    source = await decodeImage(file)
    const sourceWidth = source.width || source.naturalWidth
    const sourceHeight = source.height || source.naturalHeight
    const scale = Math.min(1, maxWidth / sourceWidth, maxHeight / sourceHeight)
    const width = Math.max(1, Math.round(sourceWidth * scale))
    const height = Math.max(1, Math.round(sourceHeight * scale))
    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height

    const context = canvas.getContext('2d', { alpha: true })
    context.imageSmoothingEnabled = true
    context.imageSmoothingQuality = 'high'
    context.drawImage(source, 0, 0, width, height)

    const outputType = 'image/webp'
    let blob = await canvasToBlob(canvas, outputType, quality)
    let nextQuality = quality
    while (blob && blob.size > maxOutputBytes && nextQuality > 0.75) {
      nextQuality = Math.max(0.75, nextQuality - 0.03)
      blob = await canvasToBlob(canvas, outputType, nextQuality)
    }

    if (!blob || blob.size > maxOutputBytes) {
      throw new Error('La imagen sigue superando el tamaño máximo después de optimizarla.')
    }

    return new File([blob], optimizedFileName(file.name, outputType), {
      type: outputType,
      lastModified: file.lastModified
    })
  } catch (error) {
    console.warn('No fue posible optimizar la imagen; se usará el archivo original.', error)
    return file
  } finally {
    if (source && typeof source.close === 'function') {
      source.close()
    }
  }
}

export function optimizeImages(files, options = {}) {
  return Promise.all(Array.from(files || [], (file) => optimizeImage(file, options)))
}
