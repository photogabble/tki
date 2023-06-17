<template>
  <canvas
      ref="mapCanvas"
      class="w-full h-full"
      @mouseenter="onMouseEnter"
      @mouseleave="onMouseLeave"
      @mousedown="onMouseDown"
      @mousemove="onMouseMove"
      @mouseup="onMouseUp"
  />
</template>

<script setup lang="ts">
import {useSeededPRNG} from "~/composables/useSeededPRNG";
import {WORLD_BORDER, MAX_TILES} from "~/constants";

const scaleX = 0.2;
const scaleY = 0.2;

const mapCanvas = ref<HTMLCanvasElement>();

const state = reactive({
  isMouseDown: false,
  currentX: 0,
  currentY: 0,

  offsetX: 0,
  offsetY: 0,

  mapOffsetX:0,
  mapOffsetZ:0,

  zoom: 0, // min: 0, max: 0.99, step: 0.01
});

const onMouseEnter = (e: MouseEvent) => {
  const target = e.target as HTMLCanvasElement;
  if(target) target.style.cursor = "grab";
};

const onMouseLeave = (e: MouseEvent) => {
  const target = e.target as HTMLCanvasElement;
  if(target) target.style.cursor = "default";

  state.isMouseDown = false;
};

const onMouseDown = (e: MouseEvent) => {
  const target = e.target as HTMLCanvasElement;
  if(target) target.style.cursor = "grabbing";

  state.isMouseDown = true;
  state.currentX = e.offsetX;
  state.currentY = e.offsetY;
}

const onMouseMove = (e: MouseEvent) => {
  if (!state.isMouseDown || !mapCanvas.value) return;
  const target = e.target as HTMLCanvasElement;

  // Useful for placing structures...
  // let zoom = Math.floor(MAX_TILES * state.zoom / 2);
  // let tileCount = (MAX_TILES - zoom) - zoom;
  // let x = zoom + Math.floor(e.offsetX/(mapCanvas.value.width/tileCount));
  // let z = zoom + Math.floor(e.offsetY/(mapCanvas.value.height/tileCount));
  // console.log(`(${x}, ${z})`);

  let transformedX;
  let transformedY;

  transformedX = scaleX * (e.offsetX - state.currentX);
  transformedY = scaleY * (e.offsetY - state.currentY);

  state.offsetX += transformedX;
  state.offsetY += transformedY;

  state.currentX = e.offsetX;
  state.currentY = e.offsetY;

  state.mapOffsetX = state.mapOffsetX - transformedX;
  state.mapOffsetZ = state.mapOffsetZ - transformedY;

  state.mapOffsetX = Math.max(-WORLD_BORDER,Math.min(WORLD_BORDER, state.mapOffsetX));
  state.mapOffsetZ = Math.max(-WORLD_BORDER,Math.min(WORLD_BORDER, state.mapOffsetZ));

  draw(target.getContext('2d'), target.clientWidth, target.clientHeight);
}

const onMouseUp = (e: MouseEvent) => {
  const target = e.target as HTMLCanvasElement;
  if (target) target.style.cursor = "grab";

  state.isMouseDown = false;
}

onMounted(() => {
  const target = mapCanvas.value;
  if (target) draw(target.getContext('2d'), target.clientWidth, target.clientHeight);
});

const draw = (context: CanvasRenderingContext2D|null, canvasWidth: number, canvasHeight: number) => {
  if (!context) return;
  context.clearRect(0, 0, canvasWidth, canvasHeight);
  const prng = useSeededPRNG(0); // TODO: set worldSeed

  const width = 100;
  const height = 100;
  const zoom = 1 - state.zoom;
  const light = 255 - 255; // max 0, min 0, step 1
  const lightPosition = 180; // max: 365, min: 0, step 1
  const lightHeight = 60; // max: 90, min -90, step 1
  const waterLevel = 132; // min: 0, max: 255, step 1
  const beachSize = 12; // min: 0, max: 255, step 1

  let lightHeightChange = (90-lightHeight);
  lightHeightChange /= 3-(lightHeight+90)/90;

  function calculateSlopeDirection(y0: number, y1: number, y2: number, y3: number) {
    let slopeX0 = y1 - y0;
    let slopeZ0 = y2 - y0;
    let slopeX1 = y3 - y2;
    let slopeZ1 = y3 - y1;

    let averageSlopeX = (slopeX0 + slopeX1) / 2;
    let averageSlopeZ = (slopeZ0 + slopeZ1) / 2;

    // Calculate the slope direction in radians and convert to degrees
    let slopeDirection = Math.atan2(averageSlopeZ, averageSlopeX) * 180 / Math.PI;

    // Make sure slopeDirection is within the range 0-359
    slopeDirection = (slopeDirection + 360) % 360;

    return [slopeDirection, averageSlopeX, averageSlopeZ];
  }

  function addShading(colors: Array<number>, slopeDirection : number, slopeX : number, slopeZ : number) {
    let light1 = 3 * (lightHeight+90)/180 + 1;
    let light2 = 5 - light1;

    colors[0] = Math.max(0,colors[0]-lightHeightChange/light1+Math.min(0,lightHeight));
    colors[1] = Math.max(0,colors[1]-lightHeightChange/2+Math.min(0,lightHeight));
    colors[2] = Math.max(0,colors[2]-lightHeightChange/light2+Math.min(0,lightHeight));

    let diff = Math.abs(lightPosition - slopeDirection);
    diff = diff > 180 ? 360 - diff : diff;

    if (slopeX == 0 && slopeZ == 0)
      diff = -(lightHeight - 90);

    for(let i=0; i<3; i++){
      colors[i] = Math.min(255, Math.max(0, colors[i] - diff * Math.abs((lightHeight-90)/90)));
    }

    return colors;

  }

  function terrainColorLookup(elevation: number, slopeDirection: number, slopeX: number, slopeZ: number) {
    let colors : Array<number> = [];

    if (elevation < waterLevel+beachSize) {
      colors = [Math.min(elevation/3+150*1.3,255), Math.min(elevation/3+110*1.3,215), Math.min(elevation/3*1.3,105), 1];
    } else if (elevation < 100) {
      colors = [elevation, elevation+88, elevation, 1];
    } else if (elevation < 130) {
      colors = [elevation, elevation+58, elevation, 1];
    } else if (elevation < 160) {
      colors = [elevation, Math.min(elevation+29), elevation, 1];
    } else if (elevation < 190) {
      colors = [elevation-10,elevation-10,elevation, 1];
    } else if (elevation < 220) {
      colors = [elevation-40,elevation-40,elevation-30, 1];
    } else {
      colors = [Math.min(255,elevation+10), Math.min(255,elevation+10), Math.min(255,elevation+20), 1];
    }

    colors = addShading(colors, slopeDirection, slopeX, slopeZ);

    for(let i=0; i<3; i++){
      colors[i] = Math.max(0, Math.min(255,colors[i]+light));
    }

    return colors;
  }

  function waterColorLookup(depth:number) {

    let colors : Array<number> = [0, 180-depth/2, 255-depth/4, 0.7];

    let light1 = 3 * (lightHeight+90)/180 + 1;
    let light2 = 5 - light1;

    colors[0] = Math.max(0,colors[0]-lightHeightChange/light1+Math.min(0,lightHeight));
    colors[1] = Math.max(0,colors[1]-lightHeightChange/2+Math.min(0,lightHeight));
    colors[2] = Math.max(0,colors[2]-lightHeightChange/light2+Math.min(0,lightHeight));

    for(let i=0; i<3; i++){
      colors[i] = Math.max(0,Math.min(255,colors[i]+light));
    }

    return colors;
  }

  const map = usePerlinNoise({
    width,
    height,
    offsetX: state.mapOffsetX,
    offsetZ: state.mapOffsetZ,
    persistence: 0.5, // max 1, min 0, step: .01
    octaves: 5, // max 6, min 1, step: 1
    wavelength: 133, // max 256, min 3, step 1
    amplitude: 1, // max 1, min 0, step .01
    peaks: 0.25, // max: 0.25, min 0, step 0.01
    exponent: 3.3, // max: 10, min 0.5, step 0.05
    prng,
  });

  let newZoom = (1-(1-zoom)/2);
  let tileCount = (MAX_TILES - Math.floor(MAX_TILES*(1-newZoom))) - Math.floor(MAX_TILES*(1-newZoom));

  for (let y = Math.floor(MAX_TILES*(1-newZoom)); y < height - 1 - Math.floor(MAX_TILES*(1-newZoom)); y++) {
    for (let x = Math.floor(MAX_TILES*(1-newZoom)); x < width - 1 - Math.floor(MAX_TILES*(1-newZoom)); x++) {

      let terrainAverageHeight = Math.floor(((map[x][y] + map[x+1][y] + map[x][y+1] + map[x+1][y+1]) / 4));
      // let terrainHighestHeight = Math.max(Math.max(map[x][y], map[x+1][y]), Math.max(map[x][y+1], map[x+1][y+1]));
      // let terrainLowestHeight = Math.min(Math.min(map[x][y], map[x+1][y]), Math.min(map[x][y+1], map[x+1][y+1]));

      let slopeValues = calculateSlopeDirection(map[x][y], map[x+1][y], map[x][y+1], map[x+1][y+1]);

      // Draw terrain color
      let color = terrainColorLookup(terrainAverageHeight, slopeValues[0], slopeValues[1], slopeValues[2]);

      context.fillStyle = `rgba(${color[0]}, ${color[1]}, ${color[2]}, ${color[3]})`;

      let xDiff = x - Math.floor(MAX_TILES*(1-newZoom));
      let yDiff = y - Math.floor(MAX_TILES*(1-newZoom));

      context.fillRect(Math.floor(xDiff*(canvasWidth/tileCount)), Math.floor(yDiff*(canvasHeight/tileCount)), Math.round(0.5+canvasWidth/tileCount), Math.round(0.5+canvasHeight/tileCount));

      // Overlay water color

      if (terrainAverageHeight < waterLevel) {

        color = waterColorLookup(waterLevel - terrainAverageHeight);

        context.fillStyle = `rgba(${color[0]}, ${color[1]}, ${color[2]}, ${color[3]})`;

        xDiff = x - Math.floor(MAX_TILES*(1-newZoom));
        yDiff = y - Math.floor(MAX_TILES*(1-newZoom));

        context.fillRect(Math.floor(xDiff*(canvasWidth/tileCount)), Math.floor(yDiff*(canvasHeight/tileCount)), Math.round(0.5+canvasWidth/tileCount), Math.round(0.5+canvasHeight/tileCount));
      }
    }
  }
}
</script>