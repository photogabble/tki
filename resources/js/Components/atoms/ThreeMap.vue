<template>
  <div class="relative w-full h-full bg-ui-orange-500/10">
    <div ref="el"
         class="w-full h-full"
         @mousedown="mouseDownAction"
         @mouseup="mouseUpAction"
         @wheel="mouseScrollAction"
         @mousemove="mouseMoveAction" @mouseleave="mouseUpAction"/>
    <div class="absolute top-1 left-1">Zoom[ {{Number(100 -(zoomPercent * 100)).toFixed(2) }}% ]</div>
  </div>
</template>

<script setup lang="ts">
import * as THREE from 'three';
import * as BufferGeometryUtils from 'three/addons/utils/BufferGeometryUtils.js';
import type {Scene, WebGLRenderer, PerspectiveCamera, Mesh, BufferAttribute} from "three";
import {createNoise3D} from "simplex-noise";
import alea from "alea";

const el = ref();
const mouseGrabbing = ref(false);
const mouseX = ref(0);
const mouseY = ref(0);
const zoomPercent = ref(0);

const maxZoom = 75; // bigger than sphere radius to avoid clipping the surface
const minZoom = 150;

const {width, height} = useElementSize(el);

let camera: PerspectiveCamera;
let renderer: WebGLRenderer;
let scene: Scene;

let sphere: Mesh;

const prng = alea();
const noise = createNoise3D(prng);

const generateTexture = function (canvas: HTMLCanvasElement, opacity: Number = .2) {
  let
      x, y,
      number;
  const ctx = canvas.getContext('2d');
  if (!ctx) throw new Error('Unable to get 2D context');
  for (x = 0; x < canvas.width; x++) {
    for (y = 0; y < canvas.height; y++) {
      number = noise(x,y,1) * 255;
      ctx.fillStyle = "rgba(" + number + "," + number + "," + number + "," + opacity + ")";
      ctx.fillRect(x, y, 1, 1);
    }
  }
};

watch(width, () => {
  renderer.setSize(width.value, height.value);
  camera.aspect = width.value / height.value;
});

const mouseDownAction = (e: MouseEvent) => {
  mouseGrabbing.value = true;
};

const mouseScrollAction = (e: WheelEvent) => {
  const nP = camera.position.z + (e.deltaY / 100);
  camera.position.z = Math.min(Math.max(nP, maxZoom), minZoom);
  zoomPercent.value = ((camera.position.z - maxZoom) / (minZoom - maxZoom));
}

const mouseMoveAction = (e: MouseEvent) => {
  if (!mouseGrabbing.value) return;
  let deltaX = e.clientX - mouseX.value;
  let deltaY = e.clientY - mouseY.value;

  mouseX.value = e.clientX;
  mouseY.value = e.clientY;

  sphere.rotation.y += deltaX / 100;
  sphere.rotation.x += deltaY / 100;
};

const mouseUpAction = () => {
  mouseGrabbing.value = false;
};

const createCubeSphere = (radius: number, segments: number = 24) => {
  let geometry = new THREE.BoxGeometry(1, 1, 1, segments, segments, segments);
  let v = new THREE.Vector3(); // temp vector, for re-use

  for(let i = 0; i < geometry.attributes.position.count; i++){
    v.fromBufferAttribute(geometry.attributes.position as BufferAttribute, i);
    v.normalize().multiplyScalar(radius);
    (geometry.attributes.position as BufferAttribute).setXYZ(i, v.x, v.y, v.z);
  }
  geometry.computeVertexNormals();

  return  BufferGeometryUtils.mergeVertices(geometry);
}

onMounted(async () => {
  if (!el.value) return;

  scene = new THREE.Scene();
  camera = new THREE.PerspectiveCamera(75, 1, 0.1, 1000);
  renderer = new THREE.WebGLRenderer();

  const tCanvas = document.createElement("canvas");
  tCanvas.width = 1024;
  tCanvas.height = 1024;
  generateTexture(tCanvas, .1);

  const texture = new THREE.Texture(tCanvas);
  texture.needsUpdate = true;

  // renderer.shadowMap.enabled = true;
  // renderer.shadowMap.type = THREE.PCFSoftShadowMap; // default THREE.PCFShadowMap
  renderer.setSize(1, 1);

  el.value.appendChild(renderer.domElement);

  const geometry = createCubeSphere(50); // new THREE.SphereGeometry(50, 128, 64);
  const material = new THREE.MeshPhongMaterial({color: 0x753825, wireframe: false, bumpMap: texture});
  sphere = new THREE.Mesh(geometry, material);

  const ambientLight = new THREE.AmbientLight('#FFE7FF', 0.25),
      light = new THREE.SpotLight('#FFE7FF', 15, 150);

  light.position.set(50, 50, 100);

  scene.add(ambientLight);
  scene.add(light);

  const spotLightHelper = new THREE.SpotLightHelper(light);
  scene.add(spotLightHelper);

  const positions = (sphere.geometry.attributes.position as THREE.BufferAttribute)
      .array as Array<number>

  for (let i = 0; i < positions.length; i += 3) {
    const v = new THREE.Vector3(
        positions[i],
        positions[i + 1],
        positions[i + 2]
    );

    positions[i] += noise(v.x, v.y, v.z) * 0.15;
    positions[i + 1] += noise(v.x, v.y, v.z) * 0.15;
    positions[i + 2] += noise(v.x, v.y, v.z) * 0.15;

    (sphere.geometry.attributes.position as THREE.BufferAttribute).needsUpdate = true
  }

  scene.add(sphere);

  const boom = new THREE.Group();
  boom.add(camera);
  scene.add(boom);
  camera.position.set(0, 0, 80); // this sets the boom's length
  camera.lookAt(0, 0, 0); // camera looks at the boom's zero
  zoomPercent.value = ((camera.position.z - maxZoom) / (minZoom - maxZoom));

  function animate() {
    requestAnimationFrame(animate);

    //sphere.rotation.x += 0.001;
    //sphere.rotation.y += 0.01;
    //boom.rotation.y += 0.01;

    //camera.position.setZ(camera.position.z +=1)
    //boom.rotation.x += 0.05;

    renderer.render(scene, camera);
  }

  animate();

});

</script>