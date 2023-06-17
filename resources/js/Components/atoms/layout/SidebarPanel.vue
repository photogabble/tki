<template>
  <div class="flex-grow h-20 flex flex-col"> <!-- needs an initial height in order to grow to available screen height -->
    <div class="uppercase border-y border-ui-orange-500/50 py-2 px-1 mb-4 border-partway-tl" style="--border-part-t-height:25%;">
      <slot name="heading"/>
    </div>
    <slot/>
  </div>
</template>