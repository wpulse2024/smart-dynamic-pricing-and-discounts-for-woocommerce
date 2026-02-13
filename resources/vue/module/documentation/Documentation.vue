<template>
  <div class="documentation-page">
    <!-- Header -->
    <!-- <header class="header">
        <div class="header-container">
          <div class="header-left">
            <button @click="toggleSidebar" class="menu-toggle">
              <span class="icon" v-if="sidebarOpen">✕</span>
              <span class="icon" v-else>☰</span>
            </button>
            <div class="logo">
              <span class="logo-icon">🏷️</span>
              <h1>WPulse Pricing Rules</h1>
            </div>
          </div>
          <div class="header-right">
            <div class="search-box">
              <span class="search-icon">🔍</span>
              <input 
                type="text" 
                v-model="searchQuery"
                placeholder="Search documentation..."
              />
            </div>
            <span class="version">v1.0.2</span>
          </div>
        </div>
      </header> -->

    <div class="main-container">
      <div class="content-wrapper">
        <!-- Sidebar -->
        <aside :class="['sidebar', { 'sidebar-open': sidebarOpen }]">
          <nav class="sidebar-nav">
            <h2 class="sidebar-title">Contents</h2>
            <ul class="nav-list">
              <li v-for="section in sections" :key="section.id" :class="{ 'active': activeSection === section.id }">
                <a @click.prevent="scrollToSection(section.id)" href="#">
                  <span class="nav-icon">{{ section.icon }}</span>
                  <span>{{ section.title }}</span>
                </a>
              </li>
            </ul>
          </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
          <!-- Introduction -->
          <section id="introduction" class="content-section">
            <h2 class="section-title">Introduction</h2>
            <div class="prose">
              <p class="lead">
                Welcome to <strong>WPulse Pricing Rules</strong> – the most powerful and flexible dynamic pricing and
                discount plugin for WooCommerce.
              </p>
              <p>
                WPulse Pricing Rules empowers store owners to create sophisticated promotional campaigns with ease. From
                simple percentage discounts to complex buy-one-get-one (BOGO) offers, quantity-based pricing tiers,
                role-specific pricing, and scheduled promotions – WPulse Pricing Rules handles it all.
              </p>
              <div class="info-box info-box-dark">
                <h3>Why Choose WPulse Pricing Rules?</h3>
                <ul>
                  <li>Increase average order value with quantity discounts</li>
                  <li>Boost customer loyalty with role-based pricing</li>
                  <li>Create urgency with time-limited promotions</li>
                  <li>Simplify complex pricing rules with an intuitive interface</li>
                  <li>Fully compatible with popular themes and plugins</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Key Features -->
          <section id="features" class="content-section">
            <h2 class="section-title">Key Features Overview</h2>
            <div class="features-grid">
              <div class="feature-card">
                <h3>3x2 & BOGO Offers</h3>
                <p>Buy 3 pay for 2, Buy 1 Get 1 Free promotions</p>
              </div>
              <div class="feature-card">
                <h3>Quantity Discounts</h3>
                <p>Bulk purchase savings and tiered pricing</p>
              </div>
              <div class="feature-card">
                <h3>Cart Discounts</h3>
                <p>Percentage or fixed amount off entire cart</p>
              </div>
              <div class="feature-card">
                <h3>Free Gifts</h3>
                <p>Add free products with qualifying purchases</p>
              </div>
              <div class="feature-card">
                <h3>Role-Based Pricing</h3>
                <p>Special pricing for wholesale, VIP customers</p>
              </div>
              <div class="feature-card">
                <h3>Category Discounts</h3>
                <p>Target specific product categories</p>
              </div>
              <div class="feature-card">
                <h3>Scheduled Promotions</h3>
                <p>Auto-start/stop based on dates</p>
              </div>
              <div class="feature-card">
                <h3>Priority Rules</h3>
                <p>Control which discounts apply first</p>
              </div>
            </div>
          </section>

          <!-- Installation -->
          <section id="installation" class="content-section">
            <h2 class="section-title">Installation Guide</h2>

            <h3 class="subsection-title">Method 1: WordPress Dashboard</h3>
            <ol class="numbered-list">
              <li>Navigate to <code>Plugins → Add New</code></li>
              <li>Search for "WPulse Pricing Rules"</li>
              <li>Click <strong>Install Now</strong> next to WPulse Pricing Rules plugin</li>
              <li>After installation, click <strong>Activate</strong></li>
              <li>You'll see a new <strong>WPulse Pricing Rules</strong> menu in your WordPress admin sidebar</li>
            </ol>

            <h3 class="subsection-title">Method 2: Manual Upload</h3>
            <ol class="numbered-list">
              <li>Download the plugin ZIP file from your purchase receipt</li>
              <li>Go to <code>Plugins → Add New → Upload Plugin</code></li>
              <li>Choose the ZIP file and click <strong>Install Now</strong></li>
              <li>Activate the plugin after installation</li>
            </ol>

            <div class="info-box info-box-blue">
              <p><strong>Note:</strong> WPulse Pricing Rules requires WooCommerce 5.0 or higher to function properly.
              </p>
            </div>
          </section>

          <!-- Getting Started -->
          <section id="getting-started" class="content-section">
            <h2 class="section-title">Getting Started</h2>

            <h3 class="subsection-title">Accessing WPulse Pricing Rules Panel</h3>
            <p>
              After activation, you'll find the WPulse Pricing Rules menu in your WordPress admin sidebar. Click on
              <strong>Rules</strong> to access the main pricing rules dashboard.
            </p>

            <h3 class="subsection-title">Creating Your First Promotion</h3>
            <ol class="numbered-list">
              <li>Click the <strong>+ Create New Rule</strong> button</li>
              <li>Choose from pre-built templates or create from scratch:
                <ul class="bullet-list">
                  <li><strong>3x2 Offer</strong> – Buy 3 items, pay for 2</li>
                  <li><strong>BOGO</strong> – Buy 1 Get 1 Free</li>
                  <li><strong>50% Off Second Unit</strong> – Half price on second item</li>
                  <li><strong>Quantity Discount</strong> – Bulk purchase savings</li>
                  <li><strong>User Role Discount</strong> – Special pricing by role</li>
                  <li><strong>Cart Discount</strong> – Percentage off entire cart</li>
                  <li><strong>Free Shipping</strong> – Free delivery on orders</li>
                </ul>
              </li>
              <li>Configure your rule settings (detailed in sections below)</li>
              <li>Click <strong>Save Rule</strong> to activate</li>
            </ol>

            <div class="tip-box">
              <h4>Quick Tip</h4>
              <p>Start with a template to understand how rules work, then customize or create your own from scratch once
                you're comfortable with the interface.</p>
            </div>
          </section>

          <!-- Creating Discounts -->
          <section id="creating-discounts" class="content-section">
            <h2 class="section-title">Creating Discounts</h2>

            <div class="discount-section">
              <h3 class="subsection-title">Quantity Discounts</h3>
              <p>Encourage bulk purchases by offering discounts based on quantity purchased.</p>
              <div class="example-box">
                <h4>Example: Buy 5+ items, get 20% off</h4>
                <ol class="numbered-list small">
                  <li>Choose <strong>Quantity Discount</strong> template</li>
                  <li>Set <strong>Purchase Quantity</strong> to 5</li>
                  <li>Set <strong>Discount Type</strong> to Percentage</li>
                  <li>Set <strong>Discount Value</strong> to 20</li>
                  <li>Select products under <strong>Product Scope</strong></li>
                </ol>
              </div>
            </div>

            <div class="discount-section">
              <h3 class="subsection-title">BOGO and 3x2 Promotions</h3>
              <p>Classic promotional offers that drive impulse purchases and increase average order value.</p>
              <div class="two-column-grid">
                <div class="example-box">
                  <h4>BOGO (Buy 1 Get 1 Free)</h4>
                  <ul class="bullet-list small">
                    <li>Purchase Quantity: 1</li>
                    <li>Discounted Items: 1</li>
                    <li>Discount Value: 100%</li>
                    <li>Apply to: All or Specific Products</li>
                  </ul>
                </div>
                <div class="example-box">
                  <h4>3x2 (Buy 3, Pay for 2)</h4>
                  <ul class="bullet-list small">
                    <li>Purchase Quantity: 3</li>
                    <li>Discounted Items: 1</li>
                    <li>Discount Value: 100%</li>
                    <li>Repeat Offer: Enable for multiple sets</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="discount-section">
              <h3 class="subsection-title">Free Gifts on Cart</h3>
              <p>Add free products automatically when customers meet certain conditions.</p>
              <div class="example-box">
                <h4>Example: Free sample with $50+ purchase</h4>
                <ol class="numbered-list small">
                  <li>Select <strong>Free Gift on Cart</strong> template</li>
                  <li>Set minimum cart total condition</li>
                  <li>Choose the free product to add</li>
                  <li>Optionally limit to one per order</li>
                </ol>
              </div>
            </div>

            <div class="discount-section">
              <h3 class="subsection-title">Cart-Level and Category Discounts</h3>
              <p>Apply discounts to the entire cart or specific product categories.</p>
              <div class="info-box info-box-light">
                <h4>Cart Discount</h4>
                <p>Choose <strong>Cart Discount</strong> template → Set percentage or fixed amount → Apply conditions
                  (minimum cart value, specific products, etc.)</p>
              </div>
              <div class="info-box info-box-light">
                <h4>Category Discount</h4>
                <p>Choose <strong>Category Discount</strong> → Select target categories → Set discount value →
                  Optionally combine with quantity rules</p>
              </div>
            </div>

            <div class="discount-section">
              <h3 class="subsection-title">Scheduled Offers</h3>
              <p>Perfect for Black Friday, Cyber Monday, seasonal sales, and limited-time promotions.</p>
              <div class="example-box">
                <h4>Example: Black Friday 30% Off Sale</h4>
                <ol class="numbered-list small">
                  <li>Create any discount rule</li>
                  <li>Scroll to <strong>Schedule</strong> section</li>
                  <li>Set <strong>Start Date</strong> (e.g., Nov 24, 2025)</li>
                  <li>Set <strong>End Date</strong> (e.g., Nov 27, 2025)</li>
                  <li>Optionally limit to specific days of the week</li>
                  <li>The promotion will automatically activate and deactivate</li>
                </ol>
              </div>
            </div>
          </section>

          <!-- User Role Pricing -->
          <section id="user-role-pricing" class="content-section">
            <h2 class="section-title">User Role Pricing</h2>
            <p>Offer special pricing to specific customer groups – perfect for wholesale customers, VIP members, or
              subscribers.</p>

            <h3 class="subsection-title">Setting Up Role-Based Discounts</h3>
            <ol class="numbered-list">
              <li>Select <strong>User Role Discount</strong> template</li>
              <li>Under <strong>User Scope</strong>, choose:
                <ul class="bullet-list">
                  <li><strong>All Users</strong> – Apply to everyone</li>
                  <li><strong>Specific Users</strong> – Select individual users</li>
                  <li><strong>User Roles</strong> – Target roles (Wholesale, VIP, Subscriber, etc.)</li>
                </ul>
              </li>
              <li>Configure discount amount and product scope</li>
              <li>Enable <strong>Apply on Sale Items</strong> and <strong>Apply on Discounted Products</strong> as
                needed</li>
            </ol>

            <div class="example-box">
              <h4>Example: 15% off for Wholesale Customers</h4>
              <ul class="bullet-list small">
                <li>User Scope: User Roles → Wholesale</li>
                <li>Discount Type: Percentage</li>
                <li>Discount Value: 15</li>
                <li>Apply to: All Products or specific categories</li>
              </ul>
            </div>
          </section>

          <!-- Scheduling -->
          <section id="scheduling" class="content-section">
            <h2 class="section-title">Scheduling Promotions</h2>
            <p>Automate your promotions with precise scheduling. Set start and end dates, and even limit promotions to
              specific days of the week.</p>

            <h3 class="subsection-title">Schedule Configuration</h3>
            <div class="info-box info-box-light">
              <h4>Start & End Dates</h4>
              <p>Use the date picker to select when your promotion begins and ends. Leave end date empty for ongoing
                promotions.</p>
            </div>
            <div class="info-box info-box-light">
              <h4>Days of Week (Optional)</h4>
              <p>Restrict promotions to specific days – great for "Weekend Sales" or "Weekday Specials".</p>
              <p class="small-text"><em>Example: Check only Saturday and Sunday for weekend-only deals</em></p>
            </div>

            <div class="info-box info-box-blue">
              <p><strong>Pro Tip:</strong> Schedule your holiday sales in advance (Black Friday, Christmas, etc.) so
                they automatically activate without manual intervention.</p>
            </div>
          </section>

          <!-- Advanced Rules -->
          <section id="advanced-rules" class="content-section">
            <h2 class="section-title">Advanced Rules</h2>

            <h3 class="subsection-title">Product Scope</h3>
            <p>Control exactly which products your discount applies to:</p>
            <div class="two-column-grid">
              <div class="info-box info-box-light">
                <h4>Apply To</h4>
                <ul class="bullet-list small">
                  <li>All Products</li>
                  <li>Specific Products</li>
                  <li>Product Categories</li>
                  <li>Product Tags</li>
                </ul>
              </div>
              <div class="info-box info-box-light">
                <h4>Exclude</h4>
                <ul class="bullet-list small">
                  <li>None</li>
                  <li>Specific Products</li>
                  <li>Categories</li>
                  <li>Tags</li>
                </ul>
              </div>
            </div>

            <h3 class="subsection-title">Priority System</h3>
            <p>When multiple rules could apply, priority determines which one takes precedence. Higher priority numbers
              win.</p>
            <div class="example-box">
              <p><strong>Example:</strong> If you have a "20% off all products" (Priority: 1) and "30% off for VIP
                users" (Priority: 2), VIP users will get the 30% discount since it has higher priority.</p>
            </div>

            <h3 class="subsection-title">Usage Limits</h3>
            <p>Control how many times a promotion can be used. Set to 0 for unlimited usage.</p>
            <div class="info-box info-box-yellow">
              <p><strong>Note:</strong> Usage limits apply globally across all users. For per-user limits, combine with
                user role restrictions.</p>
            </div>
          </section>

          <!-- Compatibility -->
          <section id="compatibility" class="content-section">
            <h2 class="section-title">Compatibility & Integrations</h2>

            <h3 class="subsection-title">Tested With</h3>
            <div class="compatibility-grid">
              <div class="compat-card">
                <h4>WordPress</h4>
                <p>Version 6.0 and above</p>
              </div>
              <div class="compat-card">
                <h4>WooCommerce</h4>
                <p>Version 5.0 and above</p>
              </div>
              <div class="compat-card">
                <h4>PHP</h4>
                <p>Version 7.4 and above</p>
              </div>
            </div>

            <h3 class="subsection-title">Compatible Plugins</h3>
            <ul class="bullet-list">
              <li><strong>WPML</strong> – Multi-language support</li>
              <li><strong>WooCommerce Subscriptions</strong> – Recurring payment discounts</li>
              <li><strong>Currency Switchers</strong> – Multi-currency compatibility</li>
              <li><strong>Custom Product Addons</strong> – Works with product variations</li>
              <li><strong>Popular Page Builders</strong> – Elementor, Divi, Beaver Builder</li>
            </ul>

            <h3 class="subsection-title">Theme Compatibility</h3>
            <p>WPulse Pricing Rules works with any properly coded WooCommerce theme. We've specifically tested with
              popular themes like Astra, OceanWP, Storefront, Flatsome, and Divi.</p>
          </section>

          <!-- Troubleshooting -->
          <section id="troubleshooting" class="content-section">
            <h2 class="section-title">Troubleshooting</h2>

            <div class="troubleshooting-item">
              <h3>Discount Not Applying</h3>
              <ul class="bullet-list small">
                <li>Check if the rule status is set to "Active"</li>
                <li>Verify product scope includes the items in cart</li>
                <li>Ensure schedule dates are correct and current</li>
                <li>Check if user role restrictions match the logged-in user</li>
                <li>Clear WooCommerce cache and browser cache</li>
              </ul>
            </div>

            <div class="troubleshooting-item">
              <h3>Wrong Discount Amount</h3>
              <ul class="bullet-list small">
                <li>Check priority settings if multiple rules are active</li>
                <li>Verify discount type (percentage vs fixed amount)</li>
                <li>Review "Apply on Sale Items" and "Apply on Discounted Products" settings</li>
                <li>Ensure no conflicting WooCommerce coupons are applied</li>
              </ul>
            </div>

            <div class="troubleshooting-item">
              <h3>Conflicting Plugins</h3>
              <ul class="bullet-list small">
                <li>Deactivate other pricing/discount plugins temporarily to test</li>
                <li>Check for conflicts with cache plugins (clear all caches)</li>
                <li>Disable JavaScript minification if rules aren't loading</li>
                <li>Contact support with a list of active plugins</li>
              </ul>
            </div>

            <div class="troubleshooting-item">
              <h3>Performance Issues</h3>
              <ul class="bullet-list small">
                <li>Limit the number of active rules (archive unused ones)</li>
                <li>Use product categories instead of individual products when possible</li>
                <li>Enable object caching on your server</li>
                <li>Consider a CDN for static assets</li>
              </ul>
            </div>

            <div class="troubleshooting-item">
              <h3>Discount Not Showing in Cart</h3>
              <ul class="bullet-list small">
                <li>Ensure WooCommerce is updated to the latest version</li>
                <li>Check theme compatibility (test with Storefront theme)</li>
                <li>Clear cart and re-add products</li>
                <li>Test with a different browser or incognito mode</li>
              </ul>
            </div>
          </section>

          <!-- FAQs -->
          <section id="faqs" class="content-section">
            <h2 class="section-title">Frequently Asked Questions</h2>

            <div class="faq-item">
              <h3>Can I combine multiple discount rules?</h3>
              <p>WPulse Pricing Rules applies the best available discount based on priority settings. By default, only
                one rule applies per product. However, you can configure rules to stack by adjusting priority levels and
                enabling "Apply on Discounted Products."</p>
            </div>

            <div class="faq-item">
              <h3>Does WPulse Pricing Rules work with WooCommerce coupons?</h3>
              <p>Yes! WPulse Pricing Rules discounts work alongside standard WooCommerce coupons. You can control
                whether WPulse Pricing Rules rules apply to already-discounted items using the "Apply on Discounted
                Products" setting in each rule.</p>
            </div>

            <div class="faq-item">
              <h3>Can I create different pricing for wholesale customers?</h3>
              <p>Absolutely! Use the User Role Discount feature to create special pricing for specific user roles.
                Perfect for B2B stores, wholesale pricing, or VIP member discounts. You can set percentage or fixed
                discounts for entire user groups.</p>
            </div>

            <div class="faq-item">
              <h3>How do I show discount badges on product pages?</h3>
              <p>WPulse Pricing Rules automatically displays discount information on product and cart pages. You can
                customize the display through your theme's template files or by using our shortcodes. Contact support
                for customization assistance.</p>
            </div>

            <div class="faq-item">
              <h3>Is there a limit to how many rules I can create?</h3>
              <p>No artificial limits! Create as many rules as you need. However, for optimal performance, we recommend
                keeping active rules under 50 and archiving seasonal or expired promotions instead of deleting them.</p>
            </div>

            <div class="faq-item">
              <h3>Can I import/export rules between sites?</h3>
              <p>Currently, import/export functionality is in development and will be available in a future update. You
                can duplicate rules within the same site using the duplicate button in the rules list.</p>
            </div>

            <div class="faq-item">
              <h3>Does WPulse Pricing Rules support variable products?</h3>
              <p>Yes! WPulse Pricing Rules fully supports WooCommerce variable products. Discounts apply to individual
                variations based on your product scope settings. You can target specific variations or apply rules to
                all variations.</p>
            </div>

            <div class="faq-item">
              <h3>Will discounts show in email notifications?</h3>
              <p>Yes, discounted prices appear in WooCommerce order emails, including new order notifications, invoices,
                and customer receipts. The discount line item shows clearly in the order summary.</p>
            </div>
          </section>

          <!-- Changelog -->
          <section id="changelog" class="content-section">
            <h2 class="section-title">Changelog</h2>

            <div class="changelog-item current">
              <h3>Version 1.0.2 - November 2025</h3>
              <p class="version-label">Current Release</p>
              <ul class="bullet-list small">
                <li><strong>Fixed:</strong> Cart discount calculation with tax-inclusive pricing</li>
                <li><strong>Fixed:</strong> Priority system not applying correctly in edge cases</li>
                <li><strong>Improved:</strong> Performance optimization for stores with 1000+ products</li>
                <li><strong>Improved:</strong> Better compatibility with currency switcher plugins</li>
                <li><strong>Added:</strong> New filter hooks for developers</li>
              </ul>
            </div>

            <div class="changelog-item">
              <h3>Version 1.0.1 - October 2025</h3>
              <ul class="bullet-list small">
                <li><strong>Fixed:</strong> BOGO offers not applying to cart correctly</li>
                <li><strong>Fixed:</strong> Schedule end date timezone issues</li>
                <li><strong>Improved:</strong> User interface responsiveness on mobile devices</li>
                <li><strong>Added:</strong> Bulk actions for managing multiple rules</li>
              </ul>
            </div>

            <div class="changelog-item">
              <h3>Version 1.0.0 - September 2025</h3>
              <ul class="bullet-list small">
                <li><strong>Initial Release:</strong> Core functionality launched</li>
                <li>Quantity discounts and BOGO offers</li>
                <li>User role-based pricing</li>
                <li>Cart and category discounts</li>
                <li>Scheduled promotions</li>
                <li>Pre-built templates</li>
                <li>Priority system for multiple rules</li>
              </ul>
            </div>
          </section>

          <!-- Support -->
          <section id="support" class="content-section">
            <h2 class="section-title">Support & Feedback</h2>

            <div class="support-hero">
              <h3>Need Help?</h3>
              <p>Our support team is here to help you get the most out of WPulse Pricing Rules. Whether you need
                technical assistance, have questions about features, or want to suggest improvements, we're just a
                message away.</p>

              <div class="support-grid">
                <div class="support-column">
                  <h4>Get Support</h4>
                  <ul class="contact-list">
                    <li>📧 Email: support@wpulse.com</li>
                    <li>🌐 Support Portal: wpulse.com/support</li>
                    <li>📖 Documentation: docs.wpulse.com</li>
                    <li>⏰ Response Time: Within 24 hours</li>
                  </ul>
                </div>
                <div class="support-column">
                  <h4>Before Contacting</h4>
                  <ul class="contact-list">
                    <li>✓ Check this documentation</li>
                    <li>✓ Review the FAQs section</li>
                    <li>✓ Try troubleshooting steps</li>
                    <li>✓ Note your WordPress & WooCommerce versions</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="info-box info-box-blue">
              <h3>Feature Requests</h3>
              <p>Have an idea for a new feature or improvement? We'd love to hear from you! Send your suggestions to
                <strong>features@wpulse.com</strong> or submit them through our support portal.</p>
              <p class="small-text">Popular feature requests are prioritized for upcoming releases.</p>
            </div>

            <div class="info-box info-box-green">
              <h3>Report a Bug</h3>
              <p>Found a bug? Help us improve WPulse Pricing Rules by reporting it. Please include:</p>
              <ul class="bullet-list small">
                <li>WordPress and WooCommerce versions</li>
                <li>WPulse Pricing Rules version</li>
                <li>Steps to reproduce the issue</li>
                <li>Screenshots or error messages</li>
                <li>List of active plugins</li>
              </ul>
              <p class="small-text">Send bug reports to <strong>bugs@wpulse.com</strong></p>
            </div>
          </section>

          <!-- Call to Action -->
          <section class="cta-section">
            <h2>Love WPulse Pricing Rules?</h2>
            <p>Help other store owners discover WPulse Pricing Rules by leaving a review on WordPress.org</p>
            <button class="cta-button">⭐ Leave a Review</button>
            <p class="cta-footer">Your feedback helps us improve and grow. Thank you for your support!</p>
          </section>

          <!-- Footer -->
          <footer class="footer">
            <p>WPulse Pricing Rules v1.0.2 | Developed by WPulse | © 2025 All Rights Reserved</p>
            <p>
              <a href="#">Privacy Policy</a>
              <span> • </span>
              <a href="#">Terms of Service</a>
              <span> • </span>
              <a href="#">WordPress.org Plugin Page</a>
            </p>
          </footer>
        </main>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SmartPricingDocumentation',
  data() {
    return {
      sidebarOpen: true,
      activeSection: 'introduction',
      searchQuery: '',
      sections: [
        { id: 'introduction', title: 'Introduction', icon: '📖' },
        { id: 'features', title: 'Key Features', icon: '⚡' },
        { id: 'installation', title: 'Installation', icon: '📥' },
        { id: 'getting-started', title: 'Getting Started', icon: '▶️' },
        { id: 'creating-discounts', title: 'Creating Discounts', icon: '🏷️' },
        { id: 'user-role-pricing', title: 'User Role Pricing', icon: '👥' },
        { id: 'scheduling', title: 'Scheduling Promotions', icon: '📅' },
        { id: 'advanced-rules', title: 'Advanced Rules', icon: '⚙️' },
        { id: 'compatibility', title: 'Compatibility', icon: '🔌' },
        { id: 'troubleshooting', title: 'Troubleshooting', icon: '⚠️' },
        { id: 'faqs', title: 'FAQs', icon: '❓' },
        { id: 'changelog', title: 'Changelog', icon: '📝' },
        { id: 'support', title: 'Support', icon: '✉️' }
      ]
    };
  },
  methods: {
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen;
    },
    scrollToSection(id) {
      this.activeSection = id;
      const element = document.getElementById(id);
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  },
  mounted() {
    // Observe sections for active state
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          this.activeSection = entry.target.id;
        }
      });
    }, { threshold: 0.3 });

    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => observer.observe(section));
  }
};
</script>

<style lang="scss" scoped>
// Variables
$primary-color: #000000;
$primary-hover: #1a1a1a;
$secondary-color: #f5f5f5;
$text-primary: #1a1a1a;
$text-secondary: #666666;
$text-light: #999999;
$border-color: #e0e0e0;
$white: #ffffff;
$blue: #3b82f6;
$blue-light: #eff6ff;
$yellow: #f59e0b;
$yellow-light: #fffbeb;
$green: #10b981;
$green-light: #f0fdf4;
$sidebar-width: 280px;

// Mixins
@mixin transition($property: all) {
  transition: $property 0.3s ease;
}

@mixin card {
  background: $white;
  border: 1px solid $border-color;
  border-radius: 8px;
  padding: 1.5rem;
}

// Reset & Base
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.documentation-page {
  min-height: 100vh;
  background: $secondary-color;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  color: $text-primary;
  line-height: 1.6;
}

// Header
.header {
  background: $primary-color;
  color: $white;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

  .header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
  }

  .header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .menu-toggle {
    background: none;
    border: none;
    color: $white;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    @include transition;

    &:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .icon {
      font-size: 1.5rem;
      display: block;
    }

    @media (min-width: 1024px) {
      display: none;
    }
  }

  .logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;

    .logo-icon {
      font-size: 1.75rem;
    }

    h1 {
      font-size: 1.25rem;
      font-weight: 700;
    }
  }

  .header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .search-box {
    position: relative;
    display: none;

    @media (min-width: 768px) {
      display: block;
    }

    .search-icon {
      position: absolute;
      left: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
    }

    input {
      padding: 0.5rem 1rem 0.5rem 2.5rem;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 6px;
      color: $white;
      width: 250px;
      outline: none;
      @include transition;

      &::placeholder {
        color: rgba(255, 255, 255, 0.6);
      }

      &:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
      }
    }
  }

  .version {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.7);
  }
}

// Main Container
.main-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

.content-wrapper {
  display: flex;
  gap: 2rem;
}

// Sidebar
.sidebar {
  width: $sidebar-width;
  flex-shrink: 0;
  display: none;

  @media (min-width: 1024px) {
    display: block;
  }

  &.sidebar-open {
    display: block;
    position: sticky;
    max-height: calc(100vh - 104px);
    left: 0;
    top: 64px;
    bottom: 0;
    background: $white;
    z-index: 90;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
    overflow-y: scroll;

    @media (min-width: 1024px) {
      position: sticky;
      top: 80px;
      box-shadow: none;
    }
  }

  .sidebar-nav {
    @include card;
  }

  .sidebar-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: $text-light;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
  }

  .nav-list {
    list-style: none;

    li {
      margin-bottom: 0.25rem;

      &.active a {
        background: $primary-color;
        color: $white;
      }
    }

    a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.625rem 0.75rem;
      color: $text-primary;
      text-decoration: none;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      @include transition;

      &:hover {
        background: $secondary-color;
      }

      .nav-icon {
        font-size: 1.125rem;
      }
    }
  }
}

// Main Content
.main-content {
  flex: 1;
  background: $white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 3rem 2rem;

  @media (min-width: 768px) {
    padding: 3rem 4rem;
  }
}

.content-section {
  margin-bottom: 4rem;

  &:last-child {
    margin-bottom: 0;
  }
}

.section-title {
  font-size: 2rem;
  font-weight: 700;
  color: $text-primary;
  margin-bottom: 1.5rem;
}

.subsection-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: $text-primary;
  margin-top: 2rem;
  margin-bottom: 1rem;
}

// Typography
.prose {
  p {
    margin-bottom: 1rem;
    color: $text-secondary;
  }

  .lead {
    font-size: 1.125rem;
    color: $text-secondary;
  }

  strong {
    color: $text-primary;
    font-weight: 600;
  }

  code {
    background: $secondary-color;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-size: 0.875em;
    font-family: 'Courier New', monospace;
  }
}

// Lists
.numbered-list,
.bullet-list {
  margin: 1rem 0;
  padding-left: 1.5rem;
  color: $text-secondary;

  li {
    margin-bottom: 0.5rem;
    line-height: 1.6;
  }

  &.small li {
    font-size: 0.875rem;
  }
}

.bullet-list {
  list-style-type: disc;

  ul {
    margin-top: 0.5rem;
    margin-left: 1.5rem;
  }
}

// Info Boxes
.info-box {
  @include card;
  margin: 1.5rem 0;

  h3,
  h4 {
    margin-bottom: 0.75rem;
    color: $text-primary;
  }

  p {
    margin-bottom: 0.5rem;
    color: $text-secondary;

    &:last-child {
      margin-bottom: 0;
    }
  }

  ul {
    list-style: disc;
    padding-left: 1.5rem;

    li {
      margin-bottom: 0.25rem;
      color: $text-secondary;
    }
  }

  &.info-box-dark {
    background: #f8f9fa;
    border-left: 4px solid $primary-color;
  }

  &.info-box-blue {
    background: $blue-light;
    border-left: 4px solid $blue;

    p,
    li {
      color: #1e3a8a;
    }
  }

  &.info-box-yellow {
    background: $yellow-light;
    border-left: 4px solid $yellow;

    p,
    li {
      color: #78350f;
    }
  }

  &.info-box-green {
    background: $green-light;
    border-left: 4px solid $green;

    p,
    li {
      color: #065f46;
    }
  }

  &.info-box-light {
    background: $secondary-color;
  }
}

.tip-box {
  @include card;
  background: $secondary-color;
  margin: 1.5rem 0;

  h4 {
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  p {
    font-size: 0.875rem;
    color: $text-secondary;
    margin: 0;
  }
}

.example-box {
  @include card;
  background: $secondary-color;
  margin: 1rem 0;

  h4 {
    font-weight: 600;
    margin-bottom: 0.75rem;
  }
}

// Grids
.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin: 1.5rem 0;
}

.feature-card {
  @include card;
  background: $secondary-color;

  h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  p {
    font-size: 0.875rem;
    color: $text-secondary;
    margin: 0;
  }
}

.two-column-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin: 1rem 0;

  @media (min-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
  }
}

.compatibility-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin: 1.5rem 0;
}

.compat-card {
  @include card;
  background: $secondary-color;
  text-align: center;

  h4 {
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  p {
    font-size: 0.875rem;
    color: $text-secondary;
    margin: 0;
  }
}

// Discount Sections
.discount-section {
  margin-bottom: 2.5rem;
}

// Troubleshooting
.troubleshooting-item {
  @include card;
  margin-bottom: 1.5rem;

  h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
  }

  ul {
    margin: 0;
  }
}

// FAQs
.faq-item {
  @include card;
  margin-bottom: 1rem;

  h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
  }

  p {
    color: $text-secondary;
    margin: 0;
  }
}

// Changelog
.changelog-item {
  border-left: 4px solid #d1d5db;
  padding-left: 1.5rem;
  margin-bottom: 2rem;

  &.current {
    border-left-color: $primary-color;
  }

  h3 {
    font-size: 1.125rem;
    font-weight: 600;
  }

  .version-label {
    font-size: 0.875rem;
    color: $text-light;
    margin-bottom: 0.5rem;
  }

  ul {
    margin-top: 0.75rem;
  }
}

// Support Section
.support-hero {
  background: linear-gradient(135deg, #1a1a1a 0%, $primary-color 100%);
  color: $white;
  border-radius: 8px;
  padding: 2rem;
  margin-bottom: 2rem;

  h3 {
    font-size: 1.75rem;
    margin-bottom: 1rem;
  }

  p {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 1.5rem;
  }
}

.support-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;

  @media (min-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
  }
}

.support-column {
  h4 {
    font-weight: 600;
    font-size: 1.125rem;
    margin-bottom: 0.75rem;
  }
}

.contact-list {
  list-style: none;
  padding: 0;

  li {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.9);
  }
}

// CTA Section
.cta-section {
  background: linear-gradient(135deg, #1a1a1a 0%, $primary-color 100%);
  color: $white;
  border-radius: 8px;
  padding: 3rem 2rem;
  text-align: center;
  margin-top: 4rem;

  h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
  }

  p {
    font-size: 1.125rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 1.5rem;
  }

  .cta-footer {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.7);
    margin-top: 1rem;
    margin-bottom: 0;
  }
}

.cta-button {
  background: $white;
  color: $primary-color;
  border: none;
  padding: 0.875rem 2rem;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  @include transition;

  &:hover {
    background: $secondary-color;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
}

// Footer
.footer {
  margin-top: 4rem;
  padding-top: 2rem;
  border-top: 1px solid $border-color;
  text-align: center;
  color: $text-light;
  font-size: 0.875rem;

  p {
    margin-bottom: 0.5rem;
  }

  a {
    color: $primary-color;
    text-decoration: none;
    @include transition;

    &:hover {
      text-decoration: underline;
    }
  }
}

// Utility Classes
.small-text {
  font-size: 0.875rem;
  color: $text-light;
}

// Responsive
@media (max-width: 1023px) {
  .main-content {
    padding: 2rem 1.5rem;
  }

  .section-title {
    font-size: 1.75rem;
  }

  .subsection-title {
    font-size: 1.25rem;
  }
}

@media (max-width: 767px) {
  .main-content {
    padding: 1.5rem 1rem;
  }

  .section-title {
    font-size: 1.5rem;
  }

  .features-grid {
    grid-template-columns: 1fr;
  }
}
</style>