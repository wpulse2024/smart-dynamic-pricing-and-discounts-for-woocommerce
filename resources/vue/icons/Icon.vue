<template>

    <div class="fraise_icon">
        <Desktop v-if="icon === 'desktop'" :size="size" />
        <Mobile v-else-if="icon === 'mobile'" :size="size" />
        <Tablet v-else-if="icon === 'tablet'" :size="size" />
        <DonationIcon v-else-if="icon === 'donation'" :size="size" />
        <OrganizationOnboardingIcon v-else-if="icon === 'organization-onboarding'" :size="size" />
        <IndividualOnboardingIcon v-else-if="icon === 'individual-onboarding'" :size="size" />
        <CurrencyIcon v-else-if="icon === 'currency'" :size="size" />
        <AddMedia v-else-if="icon === 'add-media'" :size="size" />
        <Delete v-else-if="icon === 'delete'" :size="size" />
        <SupportMe v-else-if="icon === 'support-me'" :size="size" />
        <CampaignIcon v-else-if="icon === 'campaign'" :size="size" />
        <EmptyCampaignIcon v-else-if="icon === 'empty-campaign'" :size="size" />
        <ShareIcon v-else-if="icon === 'share'" :size="size" />
        <LineChartIcon v-else-if="icon === 'line-chart'" :size="size" />
        <GroupIcon v-else-if="icon === 'group'" :size="size" />
        <WalletIcon v-else-if="icon === 'wallet'" :size="size" />
        <UploadIcon v-else-if="icon === 'upload'" :size="size" />
        <PlusIcon v-else-if="icon === 'plus'" :size="size" />
        <DashboardIcon v-else-if="icon === 'dashboard'" :size="size" />
        <ReportsIcon v-else-if="icon === 'reports'" :size="size" />
        <SettingsIcon v-else-if="icon === 'settings'" :size="size" />
        <CampaignsIcon v-else-if="icon === 'campaigns'" :size="size" />
        <DonationsIcon v-else-if="icon === 'donations'" :size="size" />
        <DonorsIcon v-else-if="icon === 'donors'" :size="size" />
        <HomeIcon v-else-if="icon === 'home'" :size="size" />
        <EditIcon v-else-if="icon === 'editor'" :size="size" />
        <MoneyIcon v-else-if="icon === 'money'" :size="size" />
        <EmailIcon v-else-if="icon === 'email'" :size="size" />
        <CustomizationIcon v-else-if="icon === 'customization'" :size="size" />
        <LinkIcon v-else-if="icon === 'link'" :size="size" />
        <MessageIcon v-else-if="icon === 'message'" :size="size" />
        <Media v-else-if="icon === 'media'" :size="size" />
        <ViewIcon v-else-if="icon === 'view'" :size="size" />
        <CancelIcon v-else-if="icon === 'cancel'" :size="size" />
        <InfoIcon v-else-if="icon === 'info'" :size="size" />
        <DonationShareIcon v-else-if="icon === 'donation-share'" :size="size" />
        <OperationIcon v-else-if="icon === 'operation'" :size="size" />
        <ActiveStatusIcon v-else-if="icon === 'active' || icon === 'completed' || icon === 'approved' || icon === 'paid' || icon === 'success'" :size="size" />
        <PendingStatusIcon v-else-if="icon === 'pending'" :size="size" />
        <WarningStatusIcon v-else-if="icon === 'warning' || icon === 'failed'" :size="size" />
        <DragIcon v-else-if="icon === 'drag'" :size="size" />
        <LoadingIcon v-else-if="icon === 'loading'" :size="size" />
        <BackIcon v-else-if="icon === 'back'" :size="size" />
        <MoreIcon v-else-if="icon === 'more'" :size="size" />
        <PrivateIcon v-else-if="icon === 'private'" :size="size" />
        <PublicIcon v-else-if="icon === 'public'" :size="size" />
        <DownIcon v-else-if="icon === 'down'" :size="size" />
        <KeyIcon v-else-if="icon === 'key'" :size="size" />
        <EditorIcon v-else-if="icon === 'editor2'" :size="size" />
        <ConfirmIcon v-else-if="icon === 'confirm'" :size="size" />
        <CopyIcon v-else-if="icon === 'copy'" :size="size" />
        <SearchIcon v-else-if="icon === 'search'" :size="size" />
        <EmailNotificationIcon v-else-if="icon === 'email-notification'" :size="size" />
        <div v-else></div>
    </div>

</template>


<script>
import Desktop from './Desktop.vue';
import Mobile from './SmartPhone.vue';
import Tablet from './Tablet.vue';
import DonationIcon from './DonationIcon.vue';
import OrganizationOnboardingIcon from './OrganizationOnboardingIcon.vue';
import IndividualOnboardingIcon from './IndividualOnboardingIcon.vue';
import CurrencyIcon from './CurrencyIcon.vue';
import AddMedia from './AddMedia.vue';
import Delete from './Delete.vue';
import SupportMe from './SupportMe.vue';
import CampaignIcon from './CampaignIcon.vue';
import EmptyCampaignIcon from './EmptyCampaignIcon.vue';
import ShareIcon from './ShareIcon.vue';
import LineChartIcon from './LineChartIcon.vue';
import GroupIcon from './GroupIcon.vue';
import WalletIcon from './WalletIcon.vue';
import UploadIcon from './UploadIcon.vue';
import PlusIcon from './PlusIcon.vue';
import DashboardIcon from './DashboardIcon.vue';
import ReportsIcon from './ReportsIcon.vue';
import SettingsIcon from './SettingsIcon.vue';
import CampaignsIcon from './CampaignsIcon.vue';
import DonationsIcon from './DonationsIcon.vue';
import DonorsIcon from './DonorIcon.vue';
import EmailIcon from './EmailIcon.vue';
import CustomizationIcon from './CustomizationIcon.vue';
import LinkIcon from './LinkIcon.vue';
import MessageIcon from './MessageIcon.vue';
import HomeIcon from './HomeIcon.vue';
import EditIcon from './EditIcon.vue';
import MoneyIcon from './MoneyIcon.vue';
import Media from './Media.vue';
import ViewIcon from './ViewIcon.vue';
import CancelIcon from './CancelIcon.vue';
import InfoIcon from './InfoIcon.vue';
import DonationShareIcon from './DonationShareIcon.vue';
import OperationIcon from './OperationIcon.vue';
import ActiveStatusIcon from './Status/ActiveStatusIcon.vue';
import PendingStatusIcon from './Status/PendingStatusIcon.vue';
import WarningStatusIcon from './Status/WarningStatusIcon.vue';
import DragIcon from './DragIcon.vue';
import LoadingIcon from './LoadingIcon.vue';
import BackIcon from './BackIcon.vue';
import MoreIcon from './MoreIcon.vue';
import PrivateIcon from './Status/PrivateIcon.vue';
import PublicIcon from './Status/PublicIcon.vue';
import DownIcon from './DownIcon.vue';
import KeyIcon from './KeyIcon.vue';
import EditorIcon from './EditorIcon.vue';
import ConfirmIcon from './ConfirmIcon.vue';
import CopyIcon from './CopyIcon.vue';
import EmailNotificationIcon from './EmailNotificationIcon.vue';
import SearchIcon from './SearchIcon.vue';

export default {
    name: "Icon",
    components: {
        Desktop,
        Mobile,
        Tablet,
        DonationIcon,
        OrganizationOnboardingIcon,
        IndividualOnboardingIcon,
        CurrencyIcon,
        AddMedia,
        Delete,
        SupportMe,
        CampaignIcon,
        EmptyCampaignIcon,
        ShareIcon,
        LineChartIcon,
        GroupIcon,
        WalletIcon,
        UploadIcon,
        PlusIcon,
        DashboardIcon,
        ReportsIcon,
        SettingsIcon,
        CampaignsIcon,
        DonationsIcon,
        DonorsIcon,
        EmailIcon,
        CustomizationIcon,
        DashboardIcon,
        LinkIcon,
        MessageIcon,
        HomeIcon,
        EditIcon,
        MoneyIcon,
        Media,
        ViewIcon,
        CancelIcon,
        InfoIcon,
        DonationShareIcon,
        OperationIcon,
        ActiveStatusIcon,
        PendingStatusIcon,
        WarningStatusIcon,
        DragIcon,
        LoadingIcon,
        BackIcon,
        MoreIcon,
        PrivateIcon,
        PublicIcon,
        DownIcon,
        KeyIcon,
        EditorIcon,
        ConfirmIcon,
        CopyIcon,
        EmailNotificationIcon,
        SearchIcon,
    },
    props: {
        icon: {
            type: String,
            required: true,
        },
        size: {
            type: String,
            default: "1em",
        },
    },
}
</script>