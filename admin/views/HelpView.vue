<template>
  <div class="help-page">
    <div class="page-header">
      <div class="page-header__content">
        <h1 class="page-header__title">Help & Documentation</h1>
      </div>
      <p class="page-header__subtitle">
        Learn how to create and manage dynamic pricing rules for your WooCommerce store
      </p>
    </div>

    <section class="help-section help-section--video">
      <h2 class="help-section__title">Overview video</h2>
      <p class="help-section__lead">
        Watch this walkthrough to see how the plugin works and how to set up your first rules.
      </p>
      <div class="help-video-wrap">
        <iframe
          class="help-video"
          src="https://youtu.be/KNTHoxCiqJQ?si=6ecBR1RkVxa7gz2C"
          title="WPulse Pricing Rules – Overview"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          allowfullscreen
        />
      </div>
      <p class="help-section__video-link">
        <a
          href="https://youtu.be/KNTHoxCiqJQ?si=6ecBR1RkVxa7gz2C"
          target="_blank"
          rel="noopener noreferrer"
          class="help-link"
        >
          Open video on YouTube ↗
        </a>
      </p>
    </section>

    <section class="help-section">
      <h2 class="help-section__title">Features at a glance</h2>
      <ul class="help-features">
        <li class="help-features__item">
          <strong>Dynamic Rules</strong> — Create rules from templates (e.g. 3 for 2, BOGO, tiered discount) or start from scratch. Each rule has a name, status (draft/active/disabled), and priority so you control which rule applies when several could match.
        </li>
        <li class="help-features__item">
          <strong>Discount types</strong> — Percent off, fixed amount off, tiered (quantity) discounts, X for Y (e.g. buy 3 pay for 2), Nth unit % off, cart % off, cart fixed off, free shipping, free gift, and category-based discounts.
        </li>
        <li class="help-features__item">
          <strong>Targeting</strong> — Apply a rule to all products or limit it to specific products or categories. You can also exclude certain products, categories, or tags from a rule.
        </li>
        <li class="help-features__item">
          <strong>Conditions</strong> — Restrict when a rule runs: cart subtotal, cart quantity, user role, user, page (cart/checkout), products in cart, total spent, order count, shipping country, and more. Only one rule message is shown on the product page (the highest-priority rule that passes conditions).
        </li>
        <li class="help-features__item">
          <strong>Schedule</strong> — Optional start and end date/time so promotions run only when you want.
        </li>
        <li class="help-features__item">
          <strong>Customization</strong> — Show a discount badge, show the discount on the shop/archive, and add a custom message (with optional shortcodes like [save_amount] and [save_percentage]).
        </li>
        <li class="help-features__item">
          <strong>Exclusion list</strong> — Globally exclude products from all pricing rules from the Exclusion List page.
        </li>
      </ul>
    </section>

    <section class="help-section">
      <h2 class="help-section__title">Quick start</h2>
      <ol class="help-steps">
        <li>Go to <router-link to="/rules" class="help-link">Dynamic Rules</router-link> and click <strong>Add new rule</strong>.</li>
        <li>Pick a template (e.g. 3 for 2, User role discount) or start from scratch.</li>
        <li>Edit the rule: set the discount, who it applies to (targets), and any conditions (e.g. user role, cart subtotal).</li>
        <li>Turn on <strong>Show discount badge</strong> or <strong>Show discount on shop</strong> if you want the message to appear on product pages and shop.</li>
        <li>Set status to <strong>Active</strong> and save. The rule will run in the cart and at checkout.</li>
      </ol>
    </section>
  </div>
</template>

<script setup>
</script>

<style scoped>
.help-page {
  padding: 0 24px 24px;
  max-width: 900px;
}

.page-header {
  margin-bottom: 24px;
}

.page-header__title {
  font-size: 24px;
  margin: 0 0 4px;
}

.page-header__subtitle {
  color: #64748b;
  margin: 0;
  font-size: 14px;
}

.help-section {
  margin-bottom: 32px;
}

.help-section__title {
  font-size: 18px;
  margin: 0 0 8px;
  font-weight: 600;
}

.help-section__lead {
  color: #475569;
  margin: 0 0 16px;
  font-size: 14px;
}

.help-section--video {
  margin-bottom: 40px;
}

.help-video-wrap {
  position: relative;
  width: 100%;
  padding-bottom: 56.25%;
  margin-bottom: 8px;
  background: #0f172a;
  border-radius: 8px;
  overflow: hidden;
}

.help-video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.help-section__video-link {
  margin: 0;
  font-size: 14px;
}

.help-features {
  list-style: none;
  margin: 0;
  padding: 0;
}

.help-features__item {
  margin-bottom: 12px;
  padding-left: 0;
  font-size: 14px;
  line-height: 1.5;
  color: #334155;
}

.help-features__item strong {
  color: #1e293b;
}

.help-steps {
  margin: 0;
  padding-left: 20px;
  font-size: 14px;
  line-height: 1.6;
  color: #334155;
}

.help-steps li {
  margin-bottom: 8px;
}

.help-link {
  color: #2271b1;
  text-decoration: none;
}

.help-link:hover {
  text-decoration: underline;
}
</style>
