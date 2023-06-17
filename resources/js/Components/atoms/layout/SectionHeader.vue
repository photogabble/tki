<template>
  <div class="flex justify-between border-b border-b-ui-orange-500/50 py-1 px-2 bg-ui-grey-900">
    <div><slot/></div>
    <div class="flex align-middle text-sm gap-1">
      <slot name="actions"/>
    </div>
  </div>
</template>