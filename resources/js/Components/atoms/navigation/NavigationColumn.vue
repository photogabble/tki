<template>
  <div class="bg-slate-700/10 w-16 p-2 border-partway-y flex flex-col" style="--border-part-b-height: 12px; --border-part-t-height: 12px;">
    <div class="flex flex-col space-y-2 mb-2 flex-grow">
      <div class="space-y-1 text-2xl">
        <NavLink :href="isLoggedIn ? '/dashboard' : '/'" title="Overview"><DashboardIcon/></NavLink> <!-- Overview -->
        <NavLink href="/explore" title="Explore universe" :disabled="!isLoggedIn"><ExploreIcon/></NavLink> <!-- Explore Universe -->
        <NavLink href="/research" title="Research and Development" :disabled="!isLoggedIn"><ResearchIcon/></NavLink> <!-- Research -->
        <NavLink href="/harvesting" title="Manage Resource Harvesting" :disabled="!isLoggedIn"><HarvestingIcon/></NavLink> <!-- Resource Harvesting -->
        <NavLink href="/manufacturing" title="Manage Manufacturing" :disabled="!isLoggedIn"><ManufacturingIcon/></NavLink> <!-- Manufacturing -->
      </div>

      <hr class="border-ui-orange-500"/>

      <div class="space-y-1 text-2xl">
        <NavLink href="/market" title="Buy/Sell in the Marketplace" :disabled="!isLoggedIn"><MarketplaceIcon/></NavLink> <!-- Marketplace -->
        <NavLink href="/ranking" title="View Player Rankings"><RankingIcon/></NavLink> <!-- Player Ranking -->
      </div>

      <!-- Decoration -->
      <div aria-hidden="true" class="decoration flex-grow border-partway-y p-1 relative text-xs overflow-hidden min-h-[100px]" style="--border-part-b-height: 12px; --border-part-t-height: 12px;">
        <small class="block right-1 top-2 absolute h-[240px]" style="writing-mode: vertical-rl;">X000.69 //////////////////////////////...</small>
        <small class="animate-pulse block absolute bottom-0 left-1">&middot;&middot;&middot;</small>
      </div>
      <!-- ./ decoration -->
    </div>

    <div class="space-y-1 text-2xl">
      <NavLink @click="openProfilePanel" title="View and modify your profile" :disabled="!isLoggedIn"><UserProfileIcon/></NavLink> <!-- Player Profile -->
      <NavLink @click="openHelpPanel" :active="isHelpPage" title="How to play"><HelpIcon/></NavLink> <!-- Player Feedback & Help -->
      <NavLink :href="route('logout')" method="post"  title="Logout" :disabled="!isLoggedIn"><LogoutIcon/></NavLink> <!-- Logout -->
    </div>
  </div>
</template>

<script setup lang="ts">
import NavLink from "@/Components/atoms/navigation/NavLink.vue";
import ExploreIcon from "@/Components/atoms/icons/ExploreIcon.vue";
import ResearchIcon from "@/Components/atoms/icons/ResearchIcon.vue";
import ManufacturingIcon from "@/Components/atoms/icons/ManufacturingIcon.vue";
import DashboardIcon from "@/Components/atoms/icons/DashboardIcon.vue";
import RankingIcon from "@/Components/atoms/icons/RankingIcon.vue";
import HarvestingIcon from "@/Components/atoms/icons/HarvestingIcon.vue";
import MarketplaceIcon from "@/Components/atoms/icons/MarketplaceIcon.vue";
import UserProfileIcon from "@/Components/atoms/icons/UserProfileIcon.vue";
import LogoutIcon from "@/Components/atoms/icons/LogoutIcon.vue";
import HelpIcon from "@/Components/atoms/icons/HelpIcon.vue";
import {useModal} from "@/Composables/useModal";
import {useAuth} from "@/Composables/useAuth";

const {isLoggedIn, logout} = useAuth();

const {openModal: openProfilePanel} = useModal('profile');
const {openModal: openHelpPanel} = useModal('help');

const isHelpPage = false; // computed<Boolean>(() => route.path.includes('help/'));
</script>

<style scoped>
@media screen and (max-height: 700px)
{
  .decoration{
    display:none;
  }
}
</style>