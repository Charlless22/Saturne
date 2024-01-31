/* Copyright (C) 2024 EVARISK <technique@evarisk.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Library javascript to enable Browser notifications
 */

/**
 * \file    js/modules/media.js
 * \ingroup saturne
 * \brief   JavaScript media file for module Saturne
 */

/**
 * Init media JS
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 *
 * @type {Object}
 */
window.saturne.media = {};

/**
 * Init rotation value of img on canvas
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 */
window.saturne.media.rotation = 0;

/**
 * Init img in canvas
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 */
window.saturne.media.img;

/**
 * Media init
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 *
 * @returns {void}
 */
window.saturne.media.init = function() {
  window.saturne.media.event();
};

/**
 * Media event
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 *
 * @returns {void}
 */
window.saturne.media.event = function() {
  $(document).on('change', '.fast-upload', window.saturne.media.uploadImage);
  $(document).on('click', '.image-rotate-left', function() { window.saturne.media.rotateImage(-90); });
  $(document).on('click', '.image-rotate-right', function() { window.saturne.media.rotateImage(90); });
  $(document).on('click', '.image-undo', window.saturne.media.undoLastDraw);
  $(document).on('click', '.image-erase', window.saturne.media.clearCanvas);
  $(document).on('click', '.image-validate', window.saturne.media.createImg);
};

window.saturne.media.uploadImage = function() {
  if (this.files && this.files[0]) {
    var reader = new FileReader();

    reader.onload = function(event) {
      $(document).find('.modal-upload-image').addClass('modal-active');
      window.saturne.media.drawImageOnCanvas(event);
    };

    reader.readAsDataURL(this.files[0]);
  }
};

/**
 * Rotate image action
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 *
 * @returns {void}
 */
window.saturne.media.rotateImage = function(degrees) {
  window.saturne.media.rotation += degrees;
  $('#canvas').css('transform', 'rotate(' + window.saturne.media.rotation + 'deg)');
};

/**
 * Undo last drawing action
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 *
 * @return {void}
 */
window.saturne.media.undoLastDraw = function() {
  let canvas = $(this).closest('.modal-upload-image').find('canvas');
  var data   = canvas[0].signaturePad.toData();
  if (data) {
    data.pop(); // remove the last dot or line
    canvas[0].signaturePad.fromData(data);
    // Redraw the image on the canvas
    window.saturne.media.drawImageOnCanvas(window.saturne.media.img);
  }
};

/**
 * Clear canvas action
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 *
 * @return {void}
 */
window.saturne.media.clearCanvas = function() {
  let canvas = $(this).closest('.modal-upload-image').find('canvas');
  canvas[0].signaturePad.clear();
  window.saturne.media.drawImageOnCanvas(window.saturne.media.img);
};

/**
 * Draw img on canvas action
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 *
 * @return {void}
 */
window.saturne.media.drawImageOnCanvas = function(event) {
  window.saturne.media.canvas = document.querySelector('#modal-upload-image0 canvas');

  window.saturne.media.canvas.signaturePad = new SignaturePad(window.saturne.media.canvas, {
    penColor: 'rgb(255, 0, 0)'
  });

  window.saturne.media.canvas.signaturePad.clear();

  // Draw the image on the canvas
  var img = new Image();
  img.src = event.target.result;
  window.saturne.media.img = event;

  img.onload = function() {
    // let ratio = Math.max(window.devicePixelRatio || 1, 1);
    // window.saturne.media.canvas.width  = window.saturne.media.canvas.offsetWidth * ratio;
    // window.saturne.media.canvas.height = window.saturne.media.canvas.offsetHeight * ratio;
    //let context = window.saturne.media.canvas.getContext('2d').scale(ratio, ratio);
    let context = window.saturne.media.canvas.getContext('2d');
    window.saturne.media.canvas.width  = 300;
    window.saturne.media.canvas.height = 400;
    context.drawImage(img, 0, 0, window.saturne.media.canvas.width, window.saturne.media.canvas.height);
  };

  window.saturne.media.rotation = 0; // Reset rotation when a new image is selected
};

/**
 * Action fast upload.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return {void}
 */
window.saturne.media.fastUpload = function() {
  let objectId         = $(this).closest('.linked-medias').find('.modal-options').attr('data-from-id')
  let objectType       = $(this).closest('.linked-medias').find('.modal-options').attr('data-from-type')
  let objectSubtype    = $(this).closest('.linked-medias').find('.modal-options').attr('data-from-subtype')
  let objectSubdir     = $(this).closest('.linked-medias').find('.modal-options').attr('data-from-subdir')
  let objectPhotoClass = $(this).closest('.linked-medias').find('.modal-options').attr('data-photo-class')

};

/**
 * create img action
 *
 * @memberof Saturne_Media
 *
 * @since   1.3.0
 * @version 1.3.0
 *
 * @return {void}
 */
window.saturne.media.createImg = function() {
  let canvas = $(this).closest('.wpeo-modal').find('canvas')[0];
  let img    = canvas.toDataURL('image/jpeg');

  let objectSubdir = $('.fast-upload-options').attr('data-from-subdir');

  let token          = window.saturne.toolbox.getToken();
  let querySeparator = window.saturne.toolbox.getQuerySeparator(document.URL);

  $.ajax({
    url: document.URL + querySeparator + 'subaction=add_img&token=' + token,
    type: 'POST',
    processData: false,
    contentType: 'application/octet-stream',
    data: JSON.stringify({
      img: img,
      objectSubdir: objectSubdir
    }),
    success: function() {
      $('.wpeo-modal').removeClass('modal-active');
    },
    error: function () {}
  });
};
