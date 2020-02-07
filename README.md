# Shopping Website

## Description
1. A page that displaysthe 5or moreproducts(images and descriptions)that may be purchased in various quantities.This, and/or an optionalproduct details page, should allow the user to select a quantity for purchase.

2. A shopping cart page that shows the selected itemsand quantitiesof each user. This page should optionally allow the user to change quantities or remove items from the cart.This page should also allow the user to finalize their purchase.


3. An account creation/editingpage where the user can create a new accountor modify current details. This page should at least collect/updatethe user'sshipping address. Other optional behavior could include password change.Optionally,account creation should be at the discretion ofthe user, so they may purchase anonymously.

4. A comments page where purchasers with accounts may rate and comment on products they have purchased with their account information.They may optionally upload photosas well as enter comment text.This page should also display comments from others to the current user before and after the purchase.

5. A login element on every page that shows the user a login dialogfor switching from anonymous shopping.The element should optionally switch to showing the user's namewhen logged in.

## Database

- Product (description, image, pricing, shipping cost)
- User (email, password, username, purchase history, shipping address)
- Comments (product, user, rating, image(s), text)
- Cart (products, quantities, user)

