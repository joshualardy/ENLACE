<?php

/**
 * Template Name: Messagerie Template
 */

// Redirect to login if not logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

get_header();

$current_user_id = get_current_user_id();
$selected_conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
$selected_recipient_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$selected_conversation = null;
$selected_other_user = null;
$messages = array();

// If user_id is provided, get or create conversation (only if no conversation_id already set)
if ($selected_recipient_id && $selected_recipient_id != $current_user_id && !$selected_conversation_id) {
    // Verify recipient user exists
    $recipient_user = get_userdata($selected_recipient_id);
    if ($recipient_user) {
        // Create or get conversation - this will store it in database
        $conversation = get_or_create_conversation($current_user_id, $selected_recipient_id);
        
        if ($conversation && isset($conversation->id)) {
            $selected_conversation_id = intval($conversation->id);
            
            // Reload conversation from database to ensure we have all data
            global $wpdb;
            $conversations_table = $wpdb->prefix . 'enlace_conversations';
            $selected_conversation = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $conversations_table WHERE id = %d AND (user1_id = %d OR user2_id = %d)",
                $selected_conversation_id, $current_user_id, $current_user_id
            ));
            
            if ($selected_conversation) {
                // Determine the other user ID from the conversation
                $other_user_id = ($selected_conversation->user1_id == $current_user_id) 
                    ? $selected_conversation->user2_id 
                    : $selected_conversation->user1_id;
                
                // Verify other user exists
                $other_user = get_userdata($other_user_id);
                if ($other_user) {
                    // Get profile data
                    $profile_data = get_user_profile_data($other_user_id);
                    
                    if ($profile_data && is_array($profile_data) && !empty($profile_data['id'])) {
                        $selected_other_user = $profile_data;
                    } else {
                        // Fallback: create minimal profile data
                        $selected_other_user = array(
                            'id' => $other_user_id,
                            'full_name' => $other_user->display_name,
                            'profile_photo_url' => '',
                            'ville' => ''
                        );
                    }
                    
                    // Get messages for this conversation
                    $messages = get_conversation_messages($selected_conversation_id, $current_user_id);
                }
            }
        }
    }
}

// If conversation_id is provided, load the conversation
if ($selected_conversation_id && !$selected_conversation) {
    global $wpdb;
    $conversations_table = $wpdb->prefix . 'enlace_conversations';
    $selected_conversation = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $conversations_table WHERE id = %d AND (user1_id = %d OR user2_id = %d)",
        $selected_conversation_id, $current_user_id, $current_user_id
    ));
    
    if ($selected_conversation) {
        $other_user_id = ($selected_conversation->user1_id == $current_user_id) 
            ? $selected_conversation->user2_id 
            : $selected_conversation->user1_id;
        
        // Verify other user exists
        $other_user = get_userdata($other_user_id);
        if ($other_user) {
            // Get profile data
            $profile_data = get_user_profile_data($other_user_id);
            
            if ($profile_data && is_array($profile_data) && !empty($profile_data['id'])) {
                $selected_other_user = $profile_data;
            } else {
                // Fallback: create minimal profile data
                $selected_other_user = array(
                    'id' => $other_user_id,
                    'full_name' => $other_user->display_name,
                    'profile_photo_url' => '',
                    'ville' => ''
                );
            }
            
            // Get messages for this conversation
            $messages = get_conversation_messages($selected_conversation_id, $current_user_id);
        }
    }
}

// Get conversations list (after potential creation)
$conversations = get_user_conversations($current_user_id);

// Get unread count
$unread_count = get_unread_messages_count($current_user_id);
?>

<div class="messagerie-page">
    <div class="container-fluid">
        <div class="row g-0">
            <!-- Conversations List Sidebar -->
            <div class="col-md-4 messagerie-sidebar">
                <div class="messagerie-sidebar-header">
                    <h2 class="messagerie-title">Messages</h2>
                    <div class="messagerie-header-actions">
                        <?php if ($unread_count > 0) : ?>
                            <span class="messagerie-unread-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                        <button type="button" class="messagerie-new-conversation-btn" id="new-conversation-btn" title="Nouvelle conversation">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="messagerie-search-box">
                    <input type="search" id="conversation-search-input" class="messagerie-search-input" autocomplete="off" placeholder="Rechercher ici...">
                    <svg class="messagerie-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                
                <div class="messagerie-conversations-list">
                    <?php if (!empty($conversations)) : ?>
                        <?php foreach ($conversations as $conv) : 
                            $is_active = ($selected_conversation_id && $selected_conversation_id == $conv['id']);
                        ?>
                            <div class="messagerie-conversation-item <?php echo $is_active ? 'active' : ''; ?>" 
                                 onclick="window.location.href='<?php echo esc_url(home_url('/messagerie?conversation_id=' . $conv['id'])); ?>'">
                                <div class="messagerie-conversation-avatar">
                                    <?php if ($conv['other_user_photo']) : ?>
                                        <img src="<?php echo esc_url($conv['other_user_photo']); ?>" alt="<?php echo esc_attr($conv['other_user_name']); ?>">
                                    <?php else : ?>
                                        <div class="messagerie-avatar-placeholder">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($conv['unread_count'] > 0) : ?>
                                        <span class="messagerie-conversation-unread"><?php echo $conv['unread_count']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="messagerie-conversation-content">
                                    <div class="messagerie-conversation-header">
                                        <h4 class="messagerie-conversation-name"><?php echo esc_html($conv['other_user_name']); ?></h4>
                                        <?php if ($conv['last_message_at']) : ?>
                                            <span class="messagerie-conversation-time">
                                                <?php echo human_time_diff(strtotime($conv['last_message_at']), current_time('timestamp')); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($conv['last_message']) : ?>
                                        <p class="messagerie-conversation-preview"><?php echo esc_html(wp_trim_words($conv['last_message'], 15)); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="messagerie-empty-state">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p>Aucune conversation</p>
                            <p class="messagerie-empty-hint">Commencez une conversation depuis un profil</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="col-md-8 messagerie-main">
                <?php if ($selected_conversation && $selected_other_user && is_array($selected_other_user) && !empty($selected_other_user['id'])) : ?>
                    <!-- Conversation Header -->
                    <div class="messagerie-header">
                        <div class="messagerie-header-user">
                            <div class="messagerie-header-avatar">
                                <?php if (isset($selected_other_user['profile_photo_url']) && !empty($selected_other_user['profile_photo_url'])) : ?>
                                    <img src="<?php echo esc_url($selected_other_user['profile_photo_url']); ?>" alt="<?php echo esc_attr(isset($selected_other_user['full_name']) ? $selected_other_user['full_name'] : 'Utilisateur'); ?>">
                                <?php else : ?>
                                    <div class="messagerie-avatar-placeholder">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="messagerie-header-info">
                                <h3 class="messagerie-header-name"><?php echo esc_html(isset($selected_other_user['full_name']) ? $selected_other_user['full_name'] : 'Utilisateur'); ?></h3>
                                <?php if (isset($selected_other_user['ville']) && !empty($selected_other_user['ville'])) : ?>
                                    <p class="messagerie-header-location"><?php echo esc_html($selected_other_user['ville']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (isset($selected_other_user['id'])) : ?>
                            <a href="<?php echo esc_url(home_url('/userprofil?user_id=' . $selected_other_user['id'])); ?>" class="messagerie-header-profile-link">
                                Voir le profil
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Messages Container -->
                    <div class="messagerie-messages-container" id="messages-container">
                        <div class="messagerie-messages-list" id="messages-list">
                            <?php if (!empty($messages)) : ?>
                                <?php 
                                $current_date = '';
                                foreach ($messages as $msg) : 
                                    $msg_date = date('Y-m-d', strtotime($msg->created_at));
                                    if ($msg_date != $current_date) {
                                        $current_date = $msg_date;
                                        echo '<div class="messagerie-date-separator">' . date_i18n('l j F Y', strtotime($msg_date)) . '</div>';
                                    }
                                    $is_own = ($msg->sender_id == $current_user_id);
                                    $sender_data = get_user_profile_data($msg->sender_id);
                                ?>
                                    <div class="messagerie-message <?php echo $is_own ? 'own' : 'other'; ?>">
                                        <?php if (!$is_own) : ?>
                                            <div class="messagerie-message-avatar">
                                                <?php if ($sender_data && $sender_data['profile_photo_url']) : ?>
                                                    <img src="<?php echo esc_url($sender_data['profile_photo_url']); ?>" alt="<?php echo esc_attr($sender_data['full_name']); ?>">
                                                <?php else : ?>
                                                    <div class="messagerie-avatar-placeholder small">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="messagerie-message-content">
                                            <div class="messagerie-message-bubble">
                                                <p class="messagerie-message-text"><?php echo nl2br(esc_html($msg->message)); ?></p>
                                                <span class="messagerie-message-time"><?php echo date_i18n('H:i', strtotime($msg->created_at)); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="messagerie-empty-conversation">
                                    <p>Aucun message pour le moment</p>
                                    <p class="messagerie-empty-hint">Envoyez le premier message !</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <?php if (!empty($selected_other_user['id'])) : ?>
                    <div class="messagerie-input-container">
                        <form id="send-message-form" class="messagerie-input-form">
                            <?php wp_nonce_field('enlace_messaging', 'messaging_nonce'); ?>
                            <input type="hidden" name="conversation_id" value="<?php echo esc_attr($selected_conversation_id); ?>">
                            <input type="hidden" name="recipient_id" value="<?php echo esc_attr($selected_other_user['id']); ?>">
                            <textarea 
                                name="message" 
                                id="message-input" 
                                class="messagerie-input" 
                                placeholder="Tapez votre message..." 
                                rows="1"
                                required></textarea>
                            <button type="submit" class="messagerie-send-btn" id="send-btn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                <?php else : ?>
                    <!-- Empty State -->
                    <div class="messagerie-empty-main">
                        <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <h3>Sélectionnez une conversation</h3>
                        <p>Choisissez une conversation dans la liste pour commencer à échanger</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Clean URL if we have user_id parameter (conversation was just created)
    <?php if ($selected_recipient_id && $selected_conversation_id && !isset($_GET['conversation_id'])) : ?>
        // Replace user_id with conversation_id in URL without reload
        const newUrl = '<?php echo esc_url(home_url('/messagerie?conversation_id=' . $selected_conversation_id)); ?>';
        window.history.replaceState({}, '', newUrl);
    <?php endif; ?>
    
    const conversationId = <?php echo $selected_conversation_id ? $selected_conversation_id : 0; ?>;
    const messagesContainer = $('#messages-container');
    const messagesList = $('#messages-list');
    const sendForm = $('#send-message-form');
    const messageInput = $('#message-input');
    const sendBtn = $('#send-btn');
    
    // Conversation search functionality
    const conversationSearchInput = $('#conversation-search-input');
    if (conversationSearchInput.length) {
        conversationSearchInput.on('input', function() {
            const query = $(this).val().toLowerCase().trim();
            const conversationItems = $('.messagerie-conversation-item');
            
            if (query === '') {
                conversationItems.show();
            } else {
                conversationItems.each(function() {
                    const name = $(this).find('.messagerie-conversation-name').text().toLowerCase();
                    const preview = $(this).find('.messagerie-conversation-preview').text().toLowerCase();
                    
                    if (name.includes(query) || preview.includes(query)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    }
    
    // Auto-resize textarea
    messageInput.on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Scroll to bottom on load
    if (messagesContainer.length) {
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }
    
    // Send message
    if (sendForm.length) {
        sendForm.on('submit', function(e) {
            e.preventDefault();
            
            const message = messageInput.val().trim();
            if (!message) {
                return;
            }
            
            // Disable button
            sendBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            const recipientId = $('input[name="recipient_id"]').val();
            if (!recipientId) {
                alert('Erreur: Destinataire non trouvé');
                sendBtn.prop('disabled', false).html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
                return;
            }
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'send_message',
                    nonce: '<?php echo wp_create_nonce('enlace_messaging'); ?>',
                    recipient_id: recipientId,
                    message: message
                },
                success: function(response) {
                    if (response.success) {
                        messageInput.val('').css('height', 'auto');
                        // Reload page to show new message with proper formatting
                        setTimeout(function() {
                            location.reload();
                        }, 300);
                    } else {
                        alert(response.data.message || 'Erreur lors de l\'envoi');
                        sendBtn.prop('disabled', false).html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
                    }
                },
                error: function() {
                    alert('Erreur de connexion');
                    sendBtn.prop('disabled', false).html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
                }
            });
        });
    }
    
    // Auto-refresh messages every 5 seconds if conversation is open
    if (conversationId) {
        setInterval(function() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'get_messages',
                    nonce: '<?php echo wp_create_nonce('enlace_messaging'); ?>',
                    conversation_id: conversationId,
                    offset: 0
                },
                success: function(response) {
                    if (response.success) {
                        const currentScroll = messagesContainer.scrollTop();
                        const isAtBottom = messagesContainer[0].scrollHeight - messagesContainer.scrollTop() - messagesContainer.height() < 100;
                        
                        // Only update if there are new messages
                        const currentCount = messagesList.find('.messagerie-message').length;
                        if (response.data.messages.length !== currentCount) {
                            // Reload to get full conversation with date separators
                            location.reload();
                        }
                    }
                }
            });
        }, 5000);
    }
    
    // New conversation modal
    const newConversationBtn = $('#new-conversation-btn');
    const newConversationModal = $('#new-conversation-modal');
    const userSearchInput = $('#user-search-input');
    const userSearchResults = $('#user-search-results');
    let searchTimeout;
    
    // Open modal
    if (newConversationBtn.length) {
        newConversationBtn.on('click', function() {
            newConversationModal.fadeIn(200);
            userSearchInput.focus();
        });
    }
    
    // Close modal
    $('.messagerie-modal-close, .messagerie-modal-overlay').on('click', function(e) {
        if (e.target === this) {
            newConversationModal.fadeOut(200);
            userSearchInput.val('');
            userSearchResults.html('');
        }
    });
    
    // Search users
    if (userSearchInput.length) {
        userSearchInput.on('input', function() {
            const query = $(this).val().trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                userSearchResults.html('');
                return;
            }
            
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'search_users_for_messaging',
                        nonce: '<?php echo wp_create_nonce('enlace_messaging'); ?>',
                        query: query
                    },
                    success: function(response) {
                        if (response.success && response.data.users.length > 0) {
                            let html = '';
                            response.data.users.forEach(function(user) {
                                html += '<div class="messagerie-user-result" data-user-id="' + user.id + '">';
                                html += '<div class="messagerie-user-result-avatar">';
                                if (user.photo) {
                                    html += '<img src="' + user.photo + '" alt="' + user.name + '">';
                                } else {
                                    html += '<div class="messagerie-avatar-placeholder small">';
                                    html += '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5"/></svg>';
                                    html += '</div>';
                                }
                                html += '</div>';
                                html += '<div class="messagerie-user-result-info">';
                                html += '<h4>' + user.name + '</h4>';
                                if (user.location) {
                                    html += '<p>' + user.location + '</p>';
                                }
                                html += '</div>';
                                html += '</div>';
                            });
                            userSearchResults.html(html);
                        } else {
                            userSearchResults.html('<div class="messagerie-no-results">Aucun utilisateur trouvé</div>');
                        }
                    },
                    error: function() {
                        userSearchResults.html('<div class="messagerie-no-results">Erreur de recherche</div>');
                    }
                });
            }, 300);
        });
    }
    
    // Select user from results
    $(document).on('click', '.messagerie-user-result', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const userId = $(this).data('user-id');
        if (userId) {
            // Close modal first
            newConversationModal.fadeOut(200);
            // Redirect to start conversation (will create conversation and display it)
            window.location.href = '<?php echo esc_url(home_url('/messagerie')); ?>?user_id=' + userId;
        }
    });
});
</script>

<!-- New Conversation Modal -->
<div class="messagerie-modal-overlay" id="new-conversation-modal" style="display: none;">
    <div class="messagerie-modal">
        <div class="messagerie-modal-header">
            <h3>Nouvelle conversation</h3>
            <button type="button" class="messagerie-modal-close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="messagerie-modal-body">
            <div class="messagerie-search-box">
                <label for="user-search-input" class="sr-only">Rechercher un utilisateur</label>
                <input type="search" id="user-search-input" class="messagerie-search-input" autocomplete="off" placeholder="Rechercher un utilisateur...">
                <svg class="messagerie-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div id="user-search-results" class="messagerie-search-results"></div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
