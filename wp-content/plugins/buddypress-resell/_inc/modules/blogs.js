/* eslint-env browser */
/* global jQuery, ajaxurl */
jQuery(($) => {
  const blogDirs = $('.blogs.dir-list')
  const blogFooter = $('#bpf-blogs-ftr')
  const userBlogs = $('body.bp-user.my-blogs #item-body div.blogs')

  if (blogDirs.length) {
    blogDirs.on('click', 'a[data-resell-blog-id]', function () {
      bpResellBlogAction($(this), 'directory')
      return false
    })
  }

  if (userBlogs.length) {
    userBlogs.on('click', 'a[data-resell-blog-id]', function () {
      bpResellBlogAction($(this))
      return false
    })
  }

  if (blogFooter.length) {
    blogFooter.on('click', 'a[data-resell-blog-id]', function () {
      bpResellBlogAction($(this))
      return false
    })
  }
})

function bpResellBlogAction (link, context = '') {
  const action = link.data('resell-action')

  let fader = link.parent()
  if (!fader.hasClass('blog-button')) {
    link.wrap('<span class="blog-button"></span>')
    fader = link.parent()
  }

  jQuery.post(ajaxurl, {
    action: 'bp_resell_blogs',
    resellData: JSON.stringify(link.data())
  },
  (response) => {
    jQuery(fader.fadeOut(200, () => {
      // toggle classes
      if (action === 'stop_resell') {
        fader.removeClass('reselling').addClass('not-reselling')
      } else if (action === 'resell') {
        fader.removeClass('not-reselling').addClass('reselling')
      }

      // add ajax response
      fader.html(response.data.button)

      // increase / decrease counts
      let countWrapper = false
      if (context === 'directory') {
        countWrapper = jQuery('#blogs-reselling span:last-child')
      }

      if (countWrapper.length) {
        if (action === 'stop_resell') {
          countWrapper.text((countWrapper.text() >> 0) - 1)
        } else if (action === 'resell') {
          countWrapper.text((countWrapper.text() >> 0) + 1)
        }
      }

      fader.fadeIn(200)
    }))
  })
}
