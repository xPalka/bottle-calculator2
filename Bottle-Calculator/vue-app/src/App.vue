<template>
  <div id="bottle-calculator-wrap">
    <section id="bottle-calculator">
      <div id="bottle-calculator-card">
        <div v-show="loading" id="bottle-calculator-spinner-loading">
          <div id="bottle-calculator-spinner"></div>
        </div>
        <div>
          <keep-alive>
            <component :is="selected">
              <canvas ref="fabricCanvas" class="canvas-overlay"></canvas>
            </component>
          </keep-alive>
        </div>
      </div>
    </section>

    <aside ref="bottleCalculatorAside">
      <div id="bottle-calculator-aside">
        <div class="bottle-calculator-card">
          <div>
            <h5 style="margin-top: 0;">{{ translations.step1 }}</h5>
            <select v-model="selectedProductIndex" @change="selectBottle">
              <option v-for="(product, index) in jsonData.products" :key="index" :value="index">
                {{ translations['productName' + product.ID] }}
              </option>
            </select>
          </div>
          <div>
            <h5>{{ translations.step2 }}</h5>
            <select v-model="selectedBottleIndex" @change="loadBottleImage">
              <option v-for="(bottle, index) in selectedProduct.bottles" :key="index" :value="index">
                {{ translations.bottle }} {{ bottle['bottle-name'] }}
              </option>
            </select>
          </div>

          <div class="button-row">
            <h5>{{ translations.step3 }}</h5>
            <input id="bottle-label-image-load" ref="fileInput" type="file" @change="uploadImage" hidden />
            <button style="width: 100%; padding: 0;">
              <label style="width: 100%; height: 100%;padding: 8px 0; display:flex; justify-content: center; align-content: center;" for="bottle-label-image-load" class="custom-file-upload">
                {{ translations.chooseFile }}
              </label>
            </button>
            <button style="width: 100% !important;" class="accept-overlay" v-if="uploadedImage" @click="acceptOverlay">
              {{ translations.placeLabel }}
            </button>
            <div v-if="!uploadedImage" style="height: 40px;"></div>
          </div>

          <div id="button-calculator-download-container">
            <h5>{{ translations.step4 }}</h5>
            <div class="bottle-download-name-container">
              <input class="bottle-download-name" type="text" v-model="bottleName" maxlength="50"
                     :placeholder="translations.enterName" />
            </div>
            <div class="bottle-download-name-container-download">
              <button class="resize-button" :disabled="bottleName === ''"
                      v-for="size in downloadSizes" :key="size.width"
                      @click="resizeAndDownloadImage(size.width, size.height)">
                {{ size.width }}x{{ size.height }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <transition name="modal">
      <DownloadModal
          v-if="showModal"
          :visible="showModal"
          :translations="translations"
          @submit="handleEmailSubmit"
          @close="showModal = false"
      />
    </transition>
  </div>
</template>

<script>
import { fabric } from 'fabric';
import { markRaw } from 'vue';
import jsonData from '../public/bottles.json';
import DownloadModal from "@/components/DownloadModal.vue";
import * as pdfjsLib from "pdfjs-dist";

export default {
  components: {DownloadModal},
  data() {
    return {
      translations: {},
      loading: true,
      selectedProductIndex: 0, // Index of the selected bottle in the array
      selectedBottleIndex: 0, // Index of the selected bottle in the array
      selected: 'appBlack',
      showModal: false,             // modal variable
      bottleName: '',
      uploadedImage: null,
      uploadedImageScale: 1,
      canvasSize: 600,
      capImage: null,
      imgOpacity: 1,
      fabricCanvas: null,
      lastWidth: 0,
      jsonData,
      bufferCanvas: null,
      bufferCtx: null,
      downloadSizes: [
        { width: 250, height: 250 },
        { width: 500, height: 500 },
        { width: 750, height: 750 },
        { width: 1000, height: 1000 },
        { width: 1500, height: 1500 },
        { width: 2000, height: 2000 }
      ],
    };
  },
  mounted() {
    this.translations = window.translations__bottleApp || {};

    pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
        "../../pdf.worker.min.mjs",
        import.meta.url
    ).toString();

    this.$nextTick(() => {
      if (typeof fabric !== 'undefined') {
        this.initFabric();
        this.loadBackgroundImage();
      } else {
        console.error('Fabric.js is not available.');
      }
    });
    window.addEventListener('resize', this.handleResize);
  },
  beforeUnmount() {
    // Usuń nasłuchiwacz zdarzenia resize
    window.removeEventListener('resize', this.handleResize);
  },
  computed: {
    selectedProduct() {
      return this.jsonData.products[this.selectedProductIndex];
    },
    selectedBottle() {
      return this.selectedProduct.bottles[this.selectedBottleIndex];
    }
  },

  methods: {
    // inicjalizacaja canvas fabric
    initFabric() {
      const canvasElement = this.$refs.fabricCanvas;
      if (!canvasElement) {
        return;
      }

      this.updateCanvasSize();

      // Initialize the Fabric.js canvas
      this.fabricCanvas = markRaw(new fabric.Canvas(canvasElement, {
        centeredScaling: true,
        backgroundColor: "transparent",
        selectionBorderColor: "rgba(255, 255, 255, 0.8)",
        selectionColor: "rgba(255, 255, 255, 0.8)",
        selectionLineWidth: 2,
        borderColor: "black",
        cornerColor: "black",
        cornerSize: 12,
        width: this.canvasSize,
        height: this.canvasSize,
        transparentCorners: true,
      }));

      // Setup buffer canvas for image processing
      this.bufferCanvas = document.createElement('canvas');
      this.bufferCanvas.width = this.canvasSize;
      this.bufferCanvas.height = this.canvasSize;
      this.bufferCtx = this.bufferCanvas.getContext('2d');
      this.loading = false;
    },

    // zaktualizuj rozmiar canvas
    updateCanvasSize() {
      const sectionElement = document.getElementById('bottle-calculator');
      const asideElement = this.$refs.bottleCalculatorAside;

      if (sectionElement && asideElement) {
        const sectionRect = sectionElement.getBoundingClientRect();
        const asideRect = asideElement.getBoundingClientRect();

        // Sprawdź, czy aside jest w tej samej linii co section lub poniżej
        // const isAsideBelow = asideRect.top >= sectionRect.top;
        const isAsideToSide = asideRect.left == sectionRect.right;

      if (isAsideToSide) {
          // Jeśli aside jest obok section, użyj wysokości aside
          this.canvasSize = asideElement.clientHeight - 2;
        } else {
          this.canvasSize = asideElement.clientWidth - 2;
        }
      } else {
        // Ustaw domyślny rozmiar jeśli elementy nie istnieją
        this.canvasSize = 600;
      }
    },

    // responsywne zmienianie szerokości
    async handleResize() {
      const currentWidth = window.innerWidth;

      if (currentWidth !== this.lastWidth) {
        this.lastWidth = currentWidth;
        this.updateCanvasSize();
        if (this.bufferCanvas) {
          this.bufferCanvas.width = this.canvasSize;
          this.bufferCanvas.height = this.canvasSize;
        }
        // if (this.fabricCanvas) {
        //   this.fabricCanvas.setWidth(this.canvasSize);
        //   this.fabricCanvas.setHeight(this.canvasSize);
        //   this.fabricCanvas.clear();
        //   this.loadBackgroundImage();
        //   this.uploadedImage = null;
        // }

      }
    },


    resizeAndDownloadImage(width, height) {
      // Wywołanie modala do zbierania emaila
      this.showModal = true;
      this.modalWidth = width;
      this.modalHeight = height;
    },
    handleEmailSubmit(email, telephone) {
      // Utworzenie tymczasowego elementu canvas
      const tempCanvas = document.createElement('canvas');

      const copyObjectsAndBackground = () => {
        tempCanvas.width = this.modalWidth;
        tempCanvas.height = this.modalHeight;
        const tempFabricCanvas = new fabric.Canvas(tempCanvas);

        const originalBackgroundImage = this.fabricCanvas.backgroundImage;
        if (originalBackgroundImage) {
          const bgClone = fabric.util.object.clone(originalBackgroundImage);
          bgClone.set({
            left: 0,
            top: 0,
            scaleX: (this.modalWidth / this.fabricCanvas.width) * originalBackgroundImage.scaleX,
            scaleY: (this.modalHeight / this.fabricCanvas.height) * originalBackgroundImage.scaleY,
          });
          tempFabricCanvas.setBackgroundImage(bgClone, tempFabricCanvas.renderAll.bind(tempFabricCanvas));
        }

        // Kopiowanie pozostałych obiektów
        this.fabricCanvas.getObjects().forEach((obj) => {
          if (obj !== originalBackgroundImage) {
            const clone = fabric.util.object.clone(obj);
            clone.set({
              scaleX: (this.modalWidth / this.fabricCanvas.width) * obj.scaleX,
              scaleY: (this.modalHeight / this.fabricCanvas.height) * obj.scaleY,
              left: (obj.left / this.fabricCanvas.width) * this.modalWidth,
              top: (obj.top / this.fabricCanvas.height) * this.modalHeight,
            });
            tempFabricCanvas.add(clone);
          }
        });

        // Renderowanie obiektów na tymczasowej kanwie
        tempFabricCanvas.renderAll();
      };

      copyObjectsAndBackground();

      // Generowanie URL dla pobrania obrazu
      const dataURL = tempCanvas.toDataURL({
        format: 'jpg',
        quality: 1.0,
      });

      // Wywołanie funkcji wysyłającej email
      const downloadLink = document.createElement("a");
      downloadLink.href = dataURL;
      downloadLink.download = this.bottleName+".jpg";
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);

      this.sendEmailWithImageLink(email, telephone);
      this.acceptOverlay();
    },

    sendEmailWithImageLink(email, telephone) {
      const data = {
        action: 'save_image_and_send_email',
        email: email,
        telephone: telephone,
        fileName: this.bottleName,
      };

      fetch(`${window.location.origin}/wp-admin/admin-ajax.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(data).toString()
      })
          .then(response => response.json())
          .catch(error => {
            console.error('Błąd:', error);
          });
    },

    // załaduj wybór butelki
    selectBottle() {
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
      this.selectedBottleIndex = 0;
      this.uploadedImage =  null;
      this.fabricCanvas.clear();
      this.loadBackgroundImage();
    },

    // załaduj wybór nakrętki
    loadBottleImage() {
      if (this.capImage  && typeof this.capImage.removeSelf === 'function') {
        this.loadBackgroundImage();
        this.capImage.removeSelf();
      } else {
        this.loadBackgroundImage();
      }
    },

    // załaduj tło canvas
    loadBackgroundImage() {
      if (!fabric) {
        console.error('Fabric.js is not available.');
        return;
      }
      const baseUri = window.location.origin;

      const selectedProduct = this.selectedProduct;
      const backgroundAbsolutePath = baseUri+selectedProduct.bottles[this.selectedBottleIndex]['bottle-plastic'];
      fabric.Image.fromURL(backgroundAbsolutePath, (bgImage) => {
        bgImage.set({
          left: 0,
          top: 0,
          scaleX: this.fabricCanvas.width / bgImage.width,
          scaleY: this.fabricCanvas.height / bgImage.height,
          selectable: false,
          evented: false
        });
        this.fabricCanvas.setBackgroundImage(bgImage, this.fabricCanvas.renderAll.bind(this.fabricCanvas));
      });

      const selectedBottle = this.selectedBottle;
      const backgroundCapAbsolutePath = baseUri+selectedBottle['cap']['image-cap'];
      fabric.Image.fromURL(backgroundCapAbsolutePath, (capImage) => {
        capImage.set({
          left: selectedBottle['cap']['cap-right'] * (this.canvasSize / 600),
          top: selectedBottle['cap']['cap-bottom'] * (this.canvasSize / 600),
          scaleX: selectedBottle['cap']['cap-scale'] * (this.canvasSize / 600),
          scaleY: selectedBottle['cap']['cap-scale'] * (this.canvasSize / 600),
          selectable: false,
          evented: false
        });
        this.capImage = capImage;

        this.capImage.removeSelf = () => {
          this.fabricCanvas.remove(this.capImage);
          this.fabricCanvas.renderAll();
          this.capImage = null;
        };

        this.fabricCanvas.add(this.capImage);
        this.fabricCanvas.renderAll();
      }, {
        crossOrigin: 'anonymous'
      });
    },

    // wgraj zdjęcie na canvas
    uploadImage(event) {
      const file = event.target.files[0];
      if (file) {
        const fileType = file.type;
        const validImageTypes = [
          "image/png",
          "image/jpeg",
          "image/jpg",
          "image/webp",
          "image/gif",
          "image/svg+xml",
          "image/bmp",
          "image/tiff",
          "image/heic",
          "image/avif"
        ];
        const isPDF = fileType === "application/pdf";

        if (validImageTypes.includes(fileType)) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.loadForegroundImage(e.target.result);
          };
          reader.readAsDataURL(file);
        } else if (isPDF) {
          this.convertPdfToJpg(file);
        } else {
          alert("Nieobsługiwany format pliku. Wybierz PNG, JPG, WEBP lub PDF.");
        }
      }
    },
    async convertPdfToJpg(pdfFile) {
      if (!pdfjsLib) {
        console.error("pdfjsLib is not available.");
        return;
      }

      const reader = new FileReader();
      reader.onload = async (e) => {
        const data = new Uint8Array(e.target.result);

        const pdf = await pdfjsLib.getDocument({ data }).promise;

        const page = await pdf.getPage(1);
        const viewport = page.getViewport({ scale: 2 });

        const canvas = document.createElement("canvas");
        const context = canvas.getContext("2d");
        canvas.width = viewport.width;
        canvas.height = viewport.height;

        await page.render({ canvasContext: context, viewport }).promise;
        const imageUrl = canvas.toDataURL("image/jpeg");

        this.loadForegroundImage(imageUrl);
      };
      reader.readAsArrayBuffer(pdfFile);
    },
    reloadForegroundImage(img) {

      this.fabricCanvas.clear();
      this.fabricCanvas.backgroundColor = '';
      this.fabricCanvas.renderAll();

      const scaleX = this.fabricCanvas.width / img.width;
      const scaleY = this.fabricCanvas.height / img.height;

      const scale = Math.min(scaleX, scaleY);

      this.uploadedImageScale = scale;

      const scaledWidth = img.width * scale;
      const scaledHeight = img.height * scale;

      const left = (this.fabricCanvas.width - scaledWidth) / 2;
      const top = (this.fabricCanvas.height - scaledHeight) / 2;

      img.set({
        left: left,
        top: top,
        scaleX: scale / 4,
        scaleY: scale / 4,
        opacity: this.imgOpacity,
        selectable: true,
        stroke: 'black',
        strokeWidth: 4,
        objectCaching: false,
        hasControls: true,
        hasRotatingPoint: false,
        lockRotation: true,
        selectionBorderColor: "rgba(255, 255, 255, 0.8)",
        selectionColor: "rgba(255, 255, 255, 0.8)",
        selectionLineWidth: 2,
        borderColor: "black",
        cornerColor: "black",
        cornerSize: 8,
        transparentCorners: false,
      });

      this.uploadedImage = img;
      this.loadBackgroundImage();
      this.fabricCanvas.add(img);
      this.fabricCanvas.renderAll();
    },
    loadForegroundImage(imageUrl) {
      fabric.Image.fromURL(imageUrl, (img) => {
        this.reloadForegroundImage(img);
      });
    },

    async asyncOperation() {
      return new Promise((resolve) => {
        setTimeout(() => {
          resolve();
        }, 100); // Simulate an async operation with a 2-second delay
      });
    },

    async acceptOverlay() {
      this.loading = (this.fabricCanvas && this.uploadedImage);
      await this.asyncOperation();

      if (this.fabricCanvas && this.uploadedImage) {
        try {
          await this.applyCylinderWarp(); // Apply the warp effect
        } catch (error) {
          console.error('An error occurred during the warp effect:', error);
        } finally {
          this.loading = false;
          await this.asyncOperation();
        }
      } else {
        console.warn('Fabric canvas or uploaded image is not initialized.');
        this.loading = false;
        await this.asyncOperation();
      }
      this.$refs.fileInput.value = '';
    },

    // zastosuj fitlr
    applyCylinderWarp() {
      if (!this.bufferCanvas || !this.uploadedImage || !this.bufferCtx) return;

      const origWidth = this.uploadedImage.width;
      const origHeight = this.uploadedImage.height;

      const marginX = (this.uploadedImage.left || 0);
      const marginY = (this.uploadedImage.top || 0);

      this.bufferCanvas.width = this.canvasSize / this.uploadedImage.scaleX;
      this.bufferCanvas.height = this.canvasSize / this.uploadedImage.scaleY;
      this.bufferCtx = this.bufferCanvas.getContext('2d');
      const flipX = this.uploadedImage.flipX || false;
      const flipY = this.uploadedImage.flipY || false;

      this.bufferCtx.clearRect(0, 0, this.bufferCanvas.width, this.bufferCanvas.height);

      if (flipX) {
        this.bufferCtx.scale(-1, 1);
        this.bufferCtx.translate(-this.bufferCanvas.width, 0);
      }

      if (flipY) {
        this.bufferCtx.scale(1, -1);
        this.bufferCtx.translate(0, (-((marginY*2)/this.uploadedImage.scaleY)-origHeight));
      }

      this.bufferCtx.drawImage(
          this.uploadedImage.getElement(),
          0, 0 ,
          origWidth, origHeight,
          marginX / this.uploadedImage.scaleX, marginY / this.uploadedImage.scaleY,
          origWidth, origHeight,
      );

      const imageData = this.bufferCtx.getImageData(0, 0, this.bufferCanvas.width, this.bufferCanvas.height);
      const data = imageData.data;
      const { width, height } = imageData;

      const newImageData = this.bufferCtx.createImageData(width, height);

      const bottleMath = this.jsonData.products[this.selectedProductIndex].bottles[this.selectedBottleIndex]['bottle-math'];
      const mathFunction = new Function('x', 'width', 'halfWidth', 'quarterWidth', `return ${bottleMath}`);

      const quarterWidth = width / 4;
      const halfWidth = width / 2;
      const sideWidth = width * this.jsonData.products[this.selectedProductIndex].bottles[this.selectedBottleIndex]['bottle-side-fraction'];

      for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
          if (x >= sideWidth && x < (width - sideWidth)) {
            let newY = y + mathFunction(x, width, halfWidth, quarterWidth);

            if (newY >= 0 && newY < height) {
              const srcIndex = (Math.floor(newY) * width + x) * 4;
              const dstIndex = (y * width + x) * 4;

              newImageData.data[dstIndex] = data[srcIndex];
              newImageData.data[dstIndex + 1] = data[srcIndex + 1];
              newImageData.data[dstIndex + 2] = data[srcIndex + 2];
              newImageData.data[dstIndex + 3] = data[srcIndex + 3];
            }
          } else {
            const srcIndex = (y * width + x) * 4;
            const dstIndex = (y * width + x) * 4;

            newImageData.data[dstIndex] = data[srcIndex];
            newImageData.data[dstIndex + 1] = data[srcIndex + 1];
            newImageData.data[dstIndex + 2] = data[srcIndex + 2];
            newImageData.data[dstIndex + 3] = data[srcIndex + 3];
          }
        }
      }

      // Put the warped image data back onto the buffer canvas
      this.bufferCtx.putImageData(newImageData, 0, 0);

      const backgroundImage = this.fabricCanvas.backgroundImage;

      if (backgroundImage) {
        const scaleX = (this.bufferCanvas.width / backgroundImage.width) || 1;
        const scaleY = (this.bufferCanvas.height / backgroundImage.height) || 1;

        backgroundImage.set({
          scaleX: scaleX,
          scaleY: scaleY,
          left: backgroundImage.left - (backgroundImage.width * scaleX) / 2,
          top: backgroundImage.top - (backgroundImage.height * scaleY) / 2,
        });

        fabric.Image.fromURL(this.bufferCanvas.toDataURL(), (fabricImage) => {
          fabricImage.set({
            left: 0,
            top: 0,
            scaleX: (this.uploadedImage.scaleX || 1),
            scaleY: (this.uploadedImage.scaleY || 1),
            selectable: false,
            clipPath: backgroundImage,
            clipPathScaling: false,
          });

          // Clear the canvas and add the clipped image
          this.fabricCanvas.clear();
          this.loadBackgroundImage(); // Reload background if needed
          this.fabricCanvas.add(fabricImage);
          // this.uploadedImage = null;
          this.fabricCanvas.renderAll();
        });
      }
    }


  }
};
</script>

<style scoped>

.canvas-overlay {
  margin: 0;
  display: block;
  background-color: white;
}

</style>
