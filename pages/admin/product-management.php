<main id="product-mngmt">
  <div class="action-popup card">
  </div>

  <div class="action-popup-edit popup card"></div>
  <div class="action-popup-delete popup card"></div>

  <div class="create-product card">
    <h3>Add a product</h3>
    <form class="form-container" id="createForm">
      <div class="form-item">
        <label for="name">Name</label>
        <input type="text" name="item_name" required />
      </div>
      <div class="form-item">
        <label for="item_image">Product Image</label>
        <input type="file" name="item_image" required />
      </div>
      <div class="form-item">
        <label for="description">Description</label>
        <textarea name="item_desc" required></textarea>
      </div>
      <div class="form-item">
        <label for="price">Price</label>
        <input type="number" name="item_price" required />
      </div>
      <div class="form-item">
        <label for="stock">Stock</label>
        <input type="number" name="item_stock" required />
      </div>
      <div class="form-item">
          <label for="tags">Tags (separate by comma)</label>
          <input type="text" name="tags" required />
        </div>
      <div class="line"></div>
      <div class="btn-container">
        <button type="button" class="secondary-cta-btn" onclick="showPopup()">Cancel</button>
        <button id="create-submit" type="submit" class="primary-cta-btn">Add product</button>
      </div>
    </form>
  </div>

  <section class="section-nav --first">
    <a href="/admin" class="flex-row align-center"><i class="ti ti-arrow-left"></i> Back to dashboard</a>
    <h2>Products</h2>
  </section>
  <section class="container --first">
    <div class="tool-container">
      <button type="button" class="primary-cta-btn create-btn flex-row align-center" onclick="showPopup()">
        <i class="ti ti-plus"></i>
        Add a product
      </button>
    </div>
    <div class="table-wrapper">
      <div class="table-header">
        <h4>Id</h4>
        <h4>Name</h4>
        <h4>Price</h4>
        <h4>Description</h4>
        <h4>Tags</h4>
        <h4>Added on</h4>
      </div>
      <div class="table-content">
      </div>
  </section>
</main>