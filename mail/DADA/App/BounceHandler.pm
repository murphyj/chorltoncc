package DADA::App::BounceHandler;

use strict;
use lib qw(../../ ../../DADA/perllib);

use DADA::Config qw(!:DEFAULT);
use DADA::App::Guts;
use 5.008_001;
use DADA::App::Guts;

use Carp qw(croak carp);
use vars qw($AUTOLOAD);

my $Score_Card  = {};
my $Remove_List = {};
my $Have_Log    = 0;

my $parser;
my %allowed = (

    'config' => undef,
    parser   => undef,

);

sub new {

    my $that = shift;
    my $class = ref($that) || $that;

    my $self = {
        _permitted => \%allowed,
        %allowed,
    };

    bless $self, $class;

    my ($args) = @_;
    $self->_init($args);
    return $self;
}

sub AUTOLOAD {
    my $self = shift;
    my $type = ref($self)
      or croak "$self is not an object";

    my $name = $AUTOLOAD;
    $name =~ s/.*://;    #strip fully qualifies portion

    unless ( exists $self->{_permitted}->{$name} ) {
        croak "Can't access '$name' field in object of class $type";
    }
    if (@_) {
        return $self->{$name} = shift;
    }
    else {
        return $self->{$name};
    }
}

sub _init {

    my $self = shift;
    my ($args) = @_;

    require MIME::Parser;
    $parser = new MIME::Parser;
    $parser = optimize_mime_parser($parser);

    $self->parser($parser);

    $self->config($args);

	$Remove_List =  {};

    $self->open_log( $self->config->{Log} );

}

sub erase_score_card {

    my $self = shift;
    my ($args) = @_;

    my $list = undef;

    if ( exists( $args->{-list} ) ) {
        $list = $args->{-list};
    }
    my $l = '';

    $l .= "Removing the Bounce Score Card...\n\n";

    my @delete_list;

    if ($list) {
        @delete_list = ($list);
    }
    else {
        @delete_list = DADA::App::Guts::available_lists();
    }

    for my $list (@delete_list) {

        require DADA::App::BounceHandler::ScoreKeeper;
        my $bsk =
          DADA::App::BounceHandler::ScoreKeeper->new( { -list => $list } );

        $bsk->erase;

        $l .= "All scores for the mailing list '$_' have now been erased.\n";

    }

    return $l;

}

sub test_bounces {

    my $self = shift;
    my ($args) = @_;
    my $list;
    my $test_type;

    if ( exists( $args->{-list} ) ) {
        $list = $args->{-list};
    }

    if ( exists( $args->{-test_type} ) ) {
        $test_type = $args->{-test_type};
    }

    my $files_to_test = [];

    if ( $test_type eq 'pop3' ) {
        my ( $pop3_obj, $pop3_status, $pop3_log ) = $self->test_pop3;
        return $pop3_log;
    }
    elsif ( -d $test_type ) {
        @$files_to_test = $self->dir_list($test_type);
    }
    elsif ( -f $test_type ) {
        push( @$files_to_test, $test_type );
    }
    else {
        return "I don't know what you want me to test!\n";
    }

    if ( scalar @$files_to_test > 0 ) {
        return $self->test_files(
            {
                -list       => $list,
                -test_files => $files_to_test,
            }
        );
    }
}

sub test_pop3 {

    my $self = shift;
    require DADA::App::POP3Tools;

    my $lock_file_fh;
    if ( $self->config->{Enable_POP3_File_Locking} == 1 ) {

        $lock_file_fh = DADA::App::POP3Tools::_lock_pop3_check(
            { name => 'dada_bounce_handler.lock', } );
    }

    my ( $pop3_obj, $pop3_status, $pop3_log ) =
      DADA::App::POP3Tools::mail_pop3client_login(
        {

            server    => $self->config->{Server},
            username  => $self->config->{Username},
            password  => $self->config->{Password},
            port      => $self->config->{Port},
            USESSL    => $self->config->{USESSL},
            AUTH_MODE => $self->config->{AUTH_MODE},
        }
      );

    if ( $self->config->{Enable_POP3_File_Locking} == 1 ) {
        DADA::App::POP3Tools::_unlock_pop3_check(
            {
                name => 'dada_bounce_handler.lock',
                fh   => $lock_file_fh,
            },
        );
    }

    if ( defined($pop3_obj) ) {
        $pop3_obj->Close();
    }

    return ( $pop3_obj, $pop3_status, $pop3_log );
}

sub test_files {

    my $self = shift;
    my ($args) = @_;
    my $list;
    my $test_files;
    my $r = '';
    if ( exists( $args->{-list} ) ) {
        $list = $args->{-list};
    }

    if ( exists( $args->{-test_files} ) ) {
        $test_files = $args->{-test_files};
    }

    if ( scalar @$test_files <= 0 ) {
        $r .= "no files to test!\n";
        return $r;
    }

    my $i = 1;
    for my $testfile (@$test_files) {
        $r .= "Test #$i: $testfile\n" . '-' x 60 . "\n";
        my ( $need_to_delete, $msg_report, $rule_report ) =
          $self->parse_bounce( 
			{ 
				-message => $self->openfile($testfile), 
				-test    => 1, 
				-list    => $list, 
			} 
		);

        $r .= $msg_report;
        ++$i;
    }

    return $r;
}

sub openfile {
	

    my $self = shift;
    my $file = shift;
    my $data = undef;

    $file = make_safer($file);
	if(-e $file){ 
		# ...
	}
	else { 
		carp "file, '$file' doesn't exist!";
		return undef; 
	}
    open my $FILE, '<', $file or die $!;

    $data = do { local $/; <$FILE> };

    close($FILE);
    return $data;
}

sub dir_list {

    my $self = shift;
    my $dir  = shift;
    my $file;
    my @files;
    $dir = DADA::App::Guts::make_safer($file);
    opendir( DIR, $dir ) or die "$!";
    while ( defined( $file = readdir DIR ) ) {
        next if $file =~ /^\.\.?$/;
        $file =~ s(^.*/)();
        if ( -f $dir . '/' . $file ) {
            push( @files, $dir . '/' . $file );

        }

    }
    closedir(DIR);
    return @files;
}

sub parse_all_bounces {

    my $self = shift;
    my ($args) = @_;

    my $list;
    my $test = 0;
    my $log  = '';

    if ( exists( $args->{-list} ) ) {
        $list = $args->{-list};
    }
    if ( exists( $args->{-test} ) ) {
        $test = $args->{-test};
    }
    else {
        $test = 0;
    }

    my @all_lists_to_check = ();
    if ( defined($list) ) {
        push( @all_lists_to_check, $list );
    }
    else {

        # Guess, we'll do 'em all!
        @all_lists_to_check = available_lists();
    }	
    LISTCHECK: 
	for my $list_to_check (@all_lists_to_check) {

        my $ls =
          DADA::MailingList::Settings->new( { -list => $list_to_check } );
        my $lh =
          DADA::MailingList::Subscribers->new( { -list => $list_to_check } );

        $log .= "Checking Bounces for Mailing List: "
          . $ls->param('list_name') . "\n";

        if (   !defined( $self->config->{Server} )
            || !defined( $self->config->{Username} )
            || !defined( $self->config->{Password} ) )
        {
            $log .=
"The Server Username and/password haven't been filled out, stopping.";

            return $log;
        }
		
		if($test) { 
        	$log .= "Testing is enabled -  messages will be parsed and examined, but will not be acted upon.\n\n"
		}
		
        $log .=
          "Making POP3 Connection to " . $self->config->{Server} . "...\n";

        require DADA::App::POP3Tools;

        my $lock_file_fh;
        if ( $self->config->{Enable_POP3_File_Locking} == 1 ) {
            $lock_file_fh = DADA::App::POP3Tools::_lock_pop3_check(
                { name => 'dada_bounce_handler.lock' } );
        }

        my ( $pop3_obj, $pop3status, $pop3log ) =
          DADA::App::POP3Tools::mail_pop3client_login(
            {
                server    => $self->config->{Server},
                username  => $self->config->{Username},
                password  => $self->config->{Password},
                port      => $self->config->{Port},
                USESSL    => $self->config->{USESSL},
                AUTH_MODE => $self->config->{AUTH_MODE},
            }
          );
        if ( $pop3status != 1 ) {
            $log .= "Status returned $pop3status\n\n$pop3log";
            next LISTCHECK;
        }

        $log .= $pop3log;
        if ( $pop3status == 0 ) {
            next LISTCHECK;
        }

        my @delete_list = ();

        my @List = $pop3_obj->List;

        if ( !$List[0] ) {

            $log .= "\tNo bounces to handle.\n";
        }
        else {
		#$log .= scalar(@List) . " total messages to be handled\n"; 
		my $msg_num = 0; 
          MSGCHECK:
            for my $msg_info (@List) {
				
				$msg_num++; 
				$log .= "\n# $msg_num:\n"; 
                my $need_to_delete = undef;
                my ( $msgnum, $msgsize ) = split( '\s+', $msg_info );

                if ( $msgsize > $self->config->{Max_Size_Of_Any_Message} ) {
                    $log .=
                        "\tWarning! Message size ( " 
                      . $msgsize
                      . " ) is larger than the maximum size allowed ( "
                      . $self->config->{Max_Size_Of_Any_Message} . ")";
                    warn "Warning! Message size ( " 
                      . $msgsize
                      . " ) is larger than the maximum size allowed ( "
                      . $self->config->{Max_Size_Of_Any_Message} . ")";

                    $need_to_delete = 1;

                }
                else {

                    my $msg      = $pop3_obj->Retrieve($msgnum);
                    my $full_msg = $msg;

                    my $msg_report  = '';
                    my $rule_report = '';
                    eval {

                        ( $need_to_delete, $msg_report, $rule_report ) =
                          $self->parse_bounce(
                            {
                                -list    => $list_to_check,
                                -message => $full_msg,
                                -test    => $test,
                            }
                          );
                    };
                    if ($@) {

                        warn
"dada_bounce_handler.pl - irrecoverable error processing message. Skipping message (sorry!): $@";
                        $log .=
"dada_bounce_handler.pl - irrecoverable error processing message. Skipping message (sorry!): $@";

                        $need_to_delete = 1;

                    }

                    $log .= $msg_report;
                    $log .= $rule_report;

					if ( $need_to_delete == 1 ) {
						if($ls->param('bounce_handler_forward_msgs_to_list_owner')){ 
							my $r = $self->forward_to_list_owner(
								{ 
									-ls_obj => $ls,
									-msg    => $full_msg
								}
							);
							if($r == 1){ 
								$log .= "Forwarding bounces message to the List Owner (" . $ls->param('list_owner_email') . ")\n"; 
							}
							else { 
								$log .= "Problems forwarding message to the List Owner!\n";
							}
						}
					}
					
					
                }

				

                if ( $need_to_delete == 1 ) {
                    push( @delete_list, $msgnum );
                }

                if ( ( $#delete_list + 1 ) >= $self->config->{MessagesAtOnce} )
                {

                    $log .=
"\n\nThe limit has been reached of the amount of messages to be looked at for this execution\n\n";
                    last MSGCHECK;

                }
            }

        }

        if (!$test) {
            for (@delete_list) {

                $log .= "deleting message #: $_\n";
                $pop3_obj->Delete($_);
            }
        }
        else {
            $log .= "Skipping Message Deletion.\n";
        }

        $pop3_obj->Close;

        if ( $self->config->{Enable_POP3_File_Locking} == 1 ) {
            DADA::App::POP3Tools::_unlock_pop3_check(
                {
                    name => 'dada_bounce_handler.lock',
                    fh   => $lock_file_fh,
                },
            );
        }

       $log .= "Finished: " . $ls->param('list_name') . "\n\n";
    }

	if (!$test) {
	    $log .= "\nSaving Scores...\n\n";
	    my $r = $self->save_scores($Score_Card);
	    $log .= $r;
	    undef $r;
	}
    if (!$test) {
        my $r = $self->remove_bounces($Remove_List);
        $log .= $r;
        undef $r;
    }

    &close_log;

    return $log;
}

sub parse_bounce {

    my $self       = shift;
    my $msg_report = '';

    my ($args)  = @_;

    my $list    = undef; 
	if(exists($args->{-list})){ 
		$list = $args->{-list};
	}
	else { 
		$msg_report .= "You MUST pass the, '-list' paramater to, parse_bounce!\n"; 
		return ( 0, $msg_report, '' );
	}
	
	
    my $test    = $args->{-test};
    my $message = $args->{-message};

    my $email       = '';
    my $found_list  = '';
    my $diagnostics = {};

    $msg_report .= '-' x 72 . "\n";

    my $entity;

    eval { $entity = $self->parser->parse_data($message) };

    ##########################################################################
    # Tests!
    # DEV: Should really be made into their own subs
    #
    # Is this a valid email message?
    if ( !$entity ) {

        warn "No MIME entity found, this message could be garbage, skipping\n";
        $msg_report .=
          "No MIME entity found, this message could be garbage, skipping\n";
        return ( 1, $msg_report, '' );

    }

    # Run it through the ringer.
    require DADA::App::BounceHandler::MessageParser;
    my $bp = DADA::App::BounceHandler::MessageParser->new;
    ( $email, $found_list, $diagnostics ) = $bp->run_all_parses($entity);

    # Test:  Can't find a list?
    if ( !$found_list ) {
		# $msg_report .= "No valid list found. Ignoring and deleting.\n\n" . $entity->as_string . "\n\n";
        $msg_report .= "No valid list found. Ignoring and deleting.\n";
        return ( 1, $msg_report, '' );
    }

    # Test:  Hey, is this a bounce from me?!
    if ( $self->bounce_from_me($entity) ) {
        $msg_report .=
          "Bounced message was sent by myself. Ignoring and deleting\n";
        warn "Bounced message was sent by myself. Ignoring and deleting";
        return ( 1, $msg_report, '' );
    }

    # Is this from a mailing list I'm currently lookin gat?
    if ( $found_list ne $list ) {
	  	$msg_report .= "Bounced message is from a different list. Skipping over.\n"; 
        # Save it for another go.
        return ( 0, $msg_report, '' );
    }

    # /Tests!
    ##########################################################################

    # OK, all tests done? Let's get to it:

    my $rule_report = '';
    $msg_report .=
      $self->generate_nerd_report( $found_list, $email, $diagnostics );
    require DADA::App::BounceHandler::Rules;
    my $bhr = DADA::App::BounceHandler::Rules->new;
    my $rule = $bhr->find_rule_to_use( $found_list, $email, $diagnostics );

    $msg_report .= "\n* Using Rule: $rule\n";
    if ( DADA::App::Guts::check_if_list_exists( -List => $found_list ) == 0 ) {
        $msg_report .=
          'List, ' . $found_list . ' doesn\'t exist. Ignoring and deleting.';
        return ( 1, $msg_report, '' );
    }

    my $lh = DADA::MailingList::Subscribers->new( { -list => $found_list } );
    if ( $lh->check_for_double_email( -Email => $email ) != 1 ) {
        $msg_report .=
"Bounced Message is from an email address that isn't subscribed to: $found_list. Ignorning.\n";
        return ( 1, $msg_report, '' );
    }

    if ( $args->{-test} != 1 ) {

        $rule_report =
          $self->carry_out_rule( $rule, $found_list, $email, $diagnostics,
            $message );
    }
    return ( 1, $msg_report, $rule_report );

}



sub forward_to_list_owner { 
	my $self   = shift; 
	my ($args) = @_; 
	my $msg; 
	
	 
	if(!exists($args->{-msg})){ 	
		croak "you MUST pass a msg in the, '-msg' paramater!"; 
	}
	else { 
		$msg = $args->{-msg}; 
	}
	if(!exists($args->{-ls_obj})){ 	
		croak "you MUST pass a DM::ML::LS object in the, '-ls_obj' paramater!"; 
	}
	
	
	my $entity; 
	eval { $entity = $self->parser->parse_data($msg) };
    if($@){ 
		carp "problem with parsing message! $@"; 
		return undef; 
	}
	
	if ($entity->head->get('To', 0)){ 
		$entity->head->delete('To'); 
	} 
	$entity->head->add('To', $args->{-ls_obj}->param('list_owner_email')); 
	
	require Email::Address; 
	$entity->head->add('X-BeenThere', Email::Address->new($self->config->{Plugin_Name}, $args->{-ls_obj}->param('admin_email'))->format); 
	
	my $header = $entity->head->as_string;
	   $header = safely_decode($header); 

	my $body   = $entity->body_as_string;	
 	   $body = safely_decode($body); 
	
	
	require DADA::Mail::Send; 
	my $mh = DADA::Mail::Send->new(
		{
			list => $args->{-ls_obj}->param('list')
		}
	); 
	
	$mh->send(
			$mh->return_headers($header),
			Body => $body
		);

	return 1; 
} 


sub bounce_from_me {
    my $self      = shift;
    my $entity    = shift;
	if($entity->head->count('X-BeenThere' > 0)){ # Uh oh.. 
		require Email::Address; 
		my @addr = Email::Address->parse($entity->head->get( 'X-BeenThere', 0 )); 
	    my $pn = 	$self->config->{Plugin_Name}; 
		for my $a(@addr){ 
			if($a->phrase =~ m/$pn/){ 
				return 1;
			}
		}
		return 0; 
	}
	else { 
		return 0; 
	}
}

sub save_scores {

    my $self  = shift;
    my $score = shift; 
    my $m     = '';

    if ( keys %$score ) {

        my @delete_list = DADA::App::Guts::available_lists();

        for my $d_list (@delete_list) {

            $m .= "\nWorking on list: $d_list\n";

            require DADA::App::BounceHandler::ScoreKeeper;
            my $bsk = DADA::App::BounceHandler::ScoreKeeper->new(
                { -list => $d_list } );

            my $list_scores = $score->{$d_list};

            my $lh =
              DADA::MailingList::Subscribers->new( { -list => $d_list } );

            for my $bouncing_email ( keys %$list_scores ) {

                if ( $lh->check_for_double_email( -Email => $bouncing_email ) ==
                    0 )
                {
                    undef( $list_scores->{$bouncing_email} );
                    delete( $list_scores->{$bouncing_email} );

                }
                else {

                    # ?
                }
            }

            my $give_back_scores = $bsk->tally_up_scores($list_scores);

            if ( keys %$give_back_scores ) {
                $m .= "\nScore Totals for $d_list:\n\n";
                for ( keys %$give_back_scores ) {
                    $m .= "\tEmail: $_\n";
                    $m .= "\tTotal Score: " . $give_back_scores->{$_} . "\n";

                }
            }

            my $removal_list = $bsk->removal_list();

            $m .= "Addresses to be removed:\n" . '-' x 72 . "\n";
            for my $bad_email (@$removal_list) {
                $Remove_List->{$d_list}->{$bad_email} = 1;
                $m .= "\t$bad_email\n";
            }

            # DEV: Hmm, this gets repeated for each list?
            $m .= "Flushing old scores\n";
            $bsk->flush_old_scores();
        }

    }
    else {

        $m .= "No scores to tally.\n";

    }
    return $m;
}

sub remove_bounces {

    my $self   = shift;
    my $report = shift;
    my $m      = '';
    $m .= "Unsubscribing addresses:\n" . '-' x 72 . "\n";

    for my $list ( keys %$report ) {

        $m .= "\nList: $list\n";

        my $lh = DADA::MailingList::Subscribers->new( { -list => $list } );
        my $ls = DADA::MailingList::Settings->new( { -list => $list } );
        my $li = $ls->get;

        my @remove_list = keys %{ $report->{$list} };

        for (@remove_list) {
            $lh->remove_subscriber( { -email => $_, } );
            $m .= "Removing: $_\n";
        }

        if (   ( $li->{black_list} == 1 )
            && ( $li->{add_unsubs_to_black_list} == 1 ) )
        {
            for my $re (@remove_list) {
                $lh->add_subscriber(
                    {
                        -email      => $re,
                        -type       => 'black_list',
                        -dupe_check => {
                            -enable  => 1,
                            -on_dupe => 'ignore_add',
                        },
                    }
                );
            }
        }

        if ( $li->{get_unsub_notice} == 1 ) {
            require DADA::App::Messages;

            my $r;

            if ( $li->{enable_bounce_logging} ) {
                require DADA::Logging::Clickthrough;
                $r = DADA::Logging::Clickthrough->new( { -list => $list } );

            }

            $m .= "\n";

            my $aa = 0;

            for my $d_email (@remove_list) {

                DADA::App::Messages::send_owner_happenings(
                    {
                        -list   => $list,
                        -email  => $d_email,
                        -role   => 'unsubscribed',
                        -lh_obj => $lh,
                        -ls_obj => $ls,
                        -note   => 'Reason: Address is bouncing messages.',
                    }
                );

                DADA::App::Messages::send_generic_email(
                    {
                        -list    => $list,
                        -email   => $d_email,
                        -ls_obj  => $ls,
                        -headers => {
                            Subject => $self->config
                              ->{Email_Unsubscribed_Because_Of_Bouncing_Subject}
                            ,
                        },
                        -body => $self->config
                          ->{Email_Unsubscribed_Because_Of_Bouncing_Message},
                        -tmpl_params => {
                            -list_settings_vars_param => { -list => $list, },
                            -subscriber_vars =>
                              { 'subscriber.email' => $d_email, },
                            -vars =>
                              { Plugin_Name => $self->config->{Plugin_Name}, },
                        },
                    }
                );
            }
        }
    }

    return $m;
}

sub carry_out_rule {

    my $self = shift;

    require DADA::App::BounceHandler::Rules;
    my $bhr   = DADA::App::BounceHandler::Rules->new;
    my $Rules = $bhr->rules;

    my ( $title, $list, $email, $diagnostics, $message ) = @_;

    my $ls = DADA::MailingList::Settings->new( { -list => $list } );


    my $actions = {};

    my $report = '';

    my $i = 0;
    for my $rule (@$Rules) {
        if ( ( keys %$rule )[0] eq $title ) {
            $actions = $Rules->[$i]->{$title}->{Action};    # wooo that was fun.
        }
        $i++;
    }

    for my $action ( keys %$actions ) {

        if ( $action eq 'add_to_score' ) {
            $report .=
              $self->add_to_score( $list, $email, $diagnostics,
                $actions->{$action} );

#            $report .=
#             $self->append_message_to_file( $list, $email, $diagnostics,
#              $actions->{$action}, $message );
#
        }
        elsif ( $action eq 'unsubscribe_bounced_email' ) {
            $report .=
              $self->unsubscribe_bounced_email( $list, $email, $diagnostics,
                $actions->{$action} );
        }
        elsif ( $action eq 'append_message_to_file' ) {
            $report .=
              $self->append_message_to_file( $list, $email, $diagnostics,
                $actions->{$action}, $message );
        }
        elsif ( $action eq 'default' ) {
            $report .=
              $self->default_action( $list, $email, $diagnostics,
                $actions->{$action}, $message );
        }
        else {
            warn "unknown rule trying to be carried out, ignoring";
        }

        if ( $ls->param('enable_bounce_logging') ) {
            if ( exists( $diagnostics->{'Simplified-Message-Id'} ) ) {
                $report .= "\nSaving bounced email report in tracker\n";
                require DADA::Logging::Clickthrough;
                my $r = DADA::Logging::Clickthrough->new( { -list => $list } );

                my $hard_bounce = 0;
                if (   $action eq 'add_to_score'
                    && $actions->{$action} eq 'hardbounce_score' )
                {
                    $hard_bounce = 1;
                }
                elsif ( $action ne 'add_to_score' ) {
                    $hard_bounce = 1;
                }
                else {
					# ... 
                }
                if ( $hard_bounce == 1 ) {
                    $r->bounce_log(
                        {
                            -type  => 'hard',
                            -mid   => $diagnostics->{'Simplified-Message-Id'},
                            -email => $email,
                        }
                    );
                }
                else {
                    $r->bounce_log(
                        {
                            -type  => 'soft',
                            -mid   => $diagnostics->{'Simplified-Message-Id'},
                            -email => $email
                        }
                    );
                }
            }
            else {
                warn
"cannot log bounced email from, '$email' for, '$list' in tracker log - no Simplified-Message-Id found. Ignoring!";
            }
        }

        log_action( $list, $email, $diagnostics,
            "$action $actions->{$action}" );
    }

    return $report;
}

sub default_action {
    my $self = shift;
    warn "Parsing... really didn't work. Ignoring and deleting bounce.";
}

sub add_to_score {

    my $self = shift;
    my ( $list, $email, $diagnostics, $action ) = @_;

    require DADA::MailingList::Settings;
    my $ls = DADA::MailingList::Settings->new( { -list => $list } );
    my $score = 0;
    if ( $action eq 'softbounce_score' ) {
        $score = $ls->param('bounce_handler_softbounce_score');
    }
    elsif ( $action eq 'hardbounce_score' ) {
        $score = $ls->param('bounce_handler_hardbounce_score');
    }
    else {
        carp "don't know what to score this with?: '$action'";
    }

    if ( $Score_Card->{$list}->{$email} ) {
        $Score_Card->{$list}->{$email} += $score;

        # Hmm. That was easy.
    }
    else {
        $Score_Card->{$list}->{$email} = $score;
    }

    return
      "* Adding  $score to Scorecard for $email. (Removal at or above score:"
      . $ls->param('bounce_handler_threshold_score') . ")\n";

}





sub unsubscribe_bounced_email {

    my $self = shift;
    my ( $list, $email, $diagnostics, $action ) = @_;
    my @delete_list;

    if ( $action eq 'from_list' ) {
        $delete_list[0] = $list;
    }
    elsif ( $action eq 'from_all_lists' ) {
        @delete_list = DADA::App::Guts::available_lists();
    }
    else {
        warn
"unknown action: '$action', no unsubscription will be made from this email!";
    }

    #$Bounce_History->{$list}->{$email} = [$diagnostics, $action];

    my $report;

    $report .= "\n";

    for (@delete_list) {
        $Remove_List->{$_}->{$email} = 1;
        $report .= "$email to be deleted off of: '$_'\n";
    }

    return $report;

}

sub append_message_to_file {

    my $self = shift;
    my ( $list, $email, $diagnostics, $action, $message ) = @_;
    my $report;


    my $file = $DADA::Config::TMP . '/bounced_messages-' . $list . '.mbox';
    $report .= "* Appending Email to '$file'\n";

    $file = DADA::App::Guts::make_safer($file);

    open( APPENDLOG, ">>$file" ) or die $!;

    chmod( $DADA::Config::FILE_CHMOD, $file );

		my $entity;
	    eval { 
			$entity = $self->parser->parse_data($message) ;
			require Email::Address;		
			require POSIX; 
		
			# This is wrong in a few ways: 
			# The should be the envelope sender, not the "From:" header
			# the date should probably be the datein the email message. 
			# We'll try this out... 	
			my $rough_from = $entity->head->get('From', 0);
			my $from_address = ( Email::Address->parse($rough_from) )[0]->address;
			print APPENDLOG 'From ' . $from_address . ' ' . POSIX::ctime(time); 
		};
		if($@){ 
			carp "problem, somewhere: $@"; 
		}	
    print APPENDLOG $message. "\n\n";
    close(APPENDLOG) or die $!;

    return $report;

}

sub generate_nerd_report {

    my $self = shift;
    my ( $list, $email, $diagnostics ) = @_;
    my $report;
    $report = "List: $list\nEmail: $email\n\n";
    for ( keys %$diagnostics ) {
        $report .= "$_: " . $diagnostics->{$_} . "\n";
    }

    return $report;

}

sub open_log {
    my $self = shift;
    my $log  = shift;
    $log = DADA::App::Guts::make_safer($log);
    if ($log) {
        open( BOUNCELOG, ">>$log" )
          or warn "Can't open bounce log at '$log' because: $!";
        chmod( $DADA::Config::FILE_CHMOD, $log );
        $Have_Log = 1;
        return 1;
    }
}

sub log_action {

    my ( $list, $email, $diagnostics, $action ) = @_;
    my $time = scalar( localtime() );

    if ($Have_Log) {
        my $d;

        # DEV: should probably be using Text::CSV (or whatever)...
        for ( keys %$diagnostics ) {
            $diagnostics->{$_} =~ s/(\n|\r)/\\n/g;

       # $diagnostics->{$_} =~ s/:/\:/g; # Or, what I'm literally meaning, here.
            $d .= $_ . ': ' . $diagnostics->{$_} . ', ';
        }
        print BOUNCELOG "[$time]\t$list\t$action\t$email\t$d\n";
    }

}

sub close_log {
    if ($Have_Log) {
        close(BOUNCELOG);
    }
}

sub rfc1893_status {

    my $self   = shift;
    my $status = shift;
    $status = $self->strip($status);

    return "" if !$status;
    my $key;

    my ( $class, $subject, $detail ) = split( /\./, $status );

    $key = 'X' . '.' . $subject . '.' . $detail;

    my %rfc1893;

    $rfc1893{'X.0.0'} = qq {  
	Other undefined status is the only undefined error code. It
	should be used for all errors for which only the class of the
	error is known.
	};

    $rfc1893{'X.1.0'} = qq { 
	X.1.0   Other address status
	
	Something about the address specified in the message caused
	this DSN.
	};

    $rfc1893{'X.1.1'} = qq { 
	X.1.1   Bad destination mailbox address
	
	The mailbox specified in the address does not exist.  For
	Internet mail names, this means the address portion to the
	left of the "@" sign is invalid.  This code is only useful
	for permanent failures.
	};

    $rfc1893{'X.1.2'} = qq { 
	X.1.2   Bad destination system address
	
	The destination system specified in the address does not
	exist or is incapable of accepting mail.  For Internet mail
	names, this means the address portion to the right of the
	"@" is invalid for mail.  This codes is only useful for
	permanent failures.
	};

    $rfc1893{'X.1.3'} = qq { 
	X.1.3   Bad destination mailbox address syntax
	
	The destination address was syntactically invalid.  This can
	apply to any field in the address.  This code is only useful
	for permanent failures.
	};

    $rfc1893{'X.1.4'} = qq { 
	X.1.4   Destination mailbox address ambiguous
	
	The mailbox address as specified matches one or more
	recipients on the destination system.  This may result if a
	heuristic address mapping algorithm is used to map the
	specified address to a local mailbox name.
	};

    $rfc1893{'X.1.5'} = qq { 
	X.1.5   Destination address valid
	
	This mailbox address as specified was valid.  This status
	code should be used for positive delivery reports.
	};

    $rfc1893{'X.1.6'} = qq { 
	X.1.6   Destination mailbox has moved, No forwarding address
	
	The mailbox address provided was at one time valid, but mail
	is no longer being accepted for that address.  This code is
	only useful for permanent failures.
	};

    $rfc1893{'X.1.7'} = qq { 
	X.1.7   Bad sender's mailbox address syntax
	
	The sender's address was syntactically invalid.  This can
	apply to any field in the address.
	};

    $rfc1893{'X.1.8'} = qq { 
	X.1.8   Bad sender's system address
	
	The sender's system specified in the address does not exist
	or is incapable of accepting return mail.  For domain names,
	this means the address portion to the right of the "@" is
	invalid for mail.
	};

    $rfc1893{'X.2.0'} = qq { 
	X.2.0   Other or undefined mailbox status
	
	The mailbox exists, but something about the destination
	mailbox has caused the sending of this DSN.
	};

    $rfc1893{'X.2.1'} = qq {  
	X.2.1   Mailbox disabled, not accepting messages
	
	The mailbox exists, but is not accepting messages.  This may
	be a permanent error if the mailbox will never be re-enabled
	or a transient error if the mailbox is only temporarily
	disabled.
	};

    $rfc1893{'X.2.2'} = qq {  
	X.2.2   Mailbox full
	
	The mailbox is full because the user has exceeded a
	per-mailbox administrative quota or physical capacity.  The
	general semantics implies that the recipient can delete
	messages to make more space available.  This code should be
	used as a persistent transient failure.
	};

    $rfc1893{'X.2.3'} = qq {  
	X.2.3   Message length exceeds administrative limit
	
	A per-mailbox administrative message length limit has been
	exceeded.  This status code should be used when the
	per-mailbox message length limit is less than the general
	system limit.  This code should be used as a permanent
	failure.
	};

    $rfc1893{'X.2.4'} = qq {  
	X.2.4   Mailing list expansion problem
	
	The mailbox is a mailing list address and the mailing list
	was unable to be expanded.  This code may represent a
	permanent failure or a persistent transient failure.
	};

    $rfc1893{'X.3.0'} = qq {  
	X.3.0   Other or undefined mail system status
	
	The destination system exists and normally accepts mail, but
	something about the system has caused the generation of this
	DSN.
	};

    $rfc1893{'X.3.1'} = qq {  
	X.3.1   Mail system full
	
	Mail system storage has been exceeded.  The general
	semantics imply that the individual recipient may not be
	able to delete material to make room for additional
	messages.  This is useful only as a persistent transient
	error.
	};

    $rfc1893{'X.3.2'} = qq {  
	X.3.2   System not accepting network messages
	
	The host on which the mailbox is resident is not accepting
	messages.  Examples of such conditions include an immanent
	shutdown, excessive load, or system maintenance.  This is
	useful for both permanent and permanent transient errors.
	};

    $rfc1893{'X.3.3'} = qq {  
	X.3.3   System not capable of selected features
	
	Selected features specified for the message are not
	supported by the destination system.  This can occur in
	gateways when features from one domain cannot be mapped onto
	the supported feature in another.
	};

    $rfc1893{'X.3.4'} = qq {  
	X.3.4   Message too big for system
	
	The message is larger than per-message size limit.  This
	limit may either be for physical or administrative reasons.
	This is useful only as a permanent error.
	};

    $rfc1893{'X.3.5'} = qq {  
	X.3.5 System incorrectly configured
	
	The system is not configured in a manner which will permit
	it to accept this message.
	};

    $rfc1893{'X.4.0'} = qq {  
	X.4.0   Other or undefined network or routing status
	
	Something went wrong with the networking, but it is not
	clear what the problem is, or the problem cannot be well
	expressed with any of the other provided detail codes.
	};

    $rfc1893{'X.4.1'} = qq {  
	X.4.1   No answer from host
	
	The outbound connection attempt was not answered, either
	because the remote system was busy, or otherwise unable to
	take a call.  This is useful only as a persistent transient
	error.
	};

    $rfc1893{'X.4.2'} = qq {  
	X.4.2   Bad connection

	
	The outbound connection was established, but was otherwise
	unable to complete the message transaction, either because
	of time-out, or inadequate connection quality. This is
	useful only as a persistent transient error.
	};

    $rfc1893{'X.4.3'} = qq {   
	X.4.3   Directory server failure
	
	The network system was unable to forward the message,
	because a directory server was unavailable.  This is useful
	only as a persistent transient error.
	
	The inability to connect to an Internet DNS server is one
	example of the directory server failure error.
	};

    $rfc1893{'X.4.4'} = qq { 
	X.4.4   Unable to route
	
	The mail system was unable to determine the next hop for the
	message because the necessary routing information was
	unavailable from the directory server. This is useful for
	both permanent and persistent transient errors.
	
	A DNS lookup returning only an SOA (Start of Administration)
	record for a domain name is one example of the unable to
	route error.
	};

    $rfc1893{'X.4.5'} = qq { 
	X.4.5   Mail system congestion
	
	The mail system was unable to deliver the message because
	the mail system was congested. This is useful only as a
	persistent transient error.
	};

    $rfc1893{'X.4.6'} = qq { 
	X.4.6   Routing loop detected
	
	A routing loop caused the message to be forwarded too many
	times, either because of incorrect routing tables or a user
	forwarding loop. This is useful only as a persistent
	transient error.
	};

    $rfc1893{'X.4.7'} = qq { 
	X.4.7   Delivery time expired
	
	The message was considered too old by the rejecting system,
	either because it remained on that host too long or because
	the time-to-live value specified by the sender of the
	message was exceeded. If possible, the code for the actual
	problem found when delivery was attempted should be returned
	rather than this code.  This is useful only as a persistent
	transient error.
	};

    $rfc1893{'X.5.0'} = qq { 
	X.5.0   Other or undefined protocol status
	
	Something was wrong with the protocol necessary to deliver
	the message to the next hop and the problem cannot be well
	expressed with any of the other provided detail codes.
	};

    $rfc1893{'X.5.1'} = qq { 
	X.5.1   Invalid command
	
	A mail transaction protocol command was issued which was
	either out of sequence or unsupported.  This is useful only
	as a permanent error.
	};

    $rfc1893{'X.5.2'} = qq { 
	X.5.2   Syntax error
	
	A mail transaction protocol command was issued which could
	not be interpreted, either because the syntax was wrong or
	the command is unrecognized. This is useful only as a
	permanent error.
	};

    $rfc1893{'X.5.3'} = qq { 
	X.5.3   Too many recipients
	
	More recipients were specified for the message than could
	have been delivered by the protocol.  This error should
	normally result in the segmentation of the message into two,
	the remainder of the recipients to be delivered on a
	subsequent delivery attempt.  It is included in this list in
	the event that such segmentation is not possible.
	};

    $rfc1893{'X.5.4'} = qq { 
	X.5.4   Invalid command arguments
	
	A valid mail transaction protocol command was issued with
	invalid arguments, either because the arguments were out of
	range or represented unrecognized features. This is useful
	only as a permanent error.
	};

    $rfc1893{'X.5.5'} = qq { 
	X.5.5   Wrong protocol version
	
	A protocol version mis-match existed which could not be
	automatically resolved by the communicating parties.
	};

    $rfc1893{'X.6.0'} = qq { 
	X.6.0   Other or undefined media error
	
	Something about the content of a message caused it to be
	considered undeliverable and the problem cannot be well
	expressed with any of the other provided detail codes.
	};

    $rfc1893{'X.6.1'} = qq { 
	X.6.1   Media not supported
	
	The media of the message is not supported by either the
	delivery protocol or the next system in the forwarding path.
	This is useful only as a permanent error.
	};

    $rfc1893{'X.6.2'} = qq { 
	X.6.2   Conversion required and prohibited
	
	The content of the message must be converted before it can
	be delivered and such conversion is not permitted.  Such
	prohibitions may be the expression of the sender in the
	message itself or the policy of the sending host.
	};

    $rfc1893{'X.6.3'} = qq { 
	X.6.3   Conversion required but not supported
	
	The message content must be converted to be forwarded but
	such conversion is not possible or is not practical by a
	host in the forwarding path.  This condition may result when
	an ESMTP gateway supports 8bit transport but is not able to
	downgrade the message to 7 bit as required for the next hop.
	};

    $rfc1893{'X.6.4'} = qq {          
	X.6.4   Conversion with loss performed
	
	This is a warning sent to the sender when message delivery
	was successfully but when the delivery required a conversion
	in which some data was lost.  This may also be a permanant
	error if the sender has indicated that conversion with loss
	is prohibited for the message.
	};

    $rfc1893{'X.6.5'} = qq {    
	X.6.5   Conversion Failed
	
	A conversion was required but was unsuccessful.  This may be
	useful as a permanent or persistent temporary notification.
	};

    $rfc1893{'X.7.0'} = qq {   
	X.7.0   Other or undefined security status
	
	Something related to security caused the message to be
	returned, and the problem cannot be well expressed with any
	of the other provided detail codes.  This status code may
	also be used when the condition cannot be further described
	because of security policies in force.
	};

    $rfc1893{'X.7.1'} = qq {  
	X.7.1   Delivery not authorized, message refused
	
	The sender is not authorized to send to the destination.
	This can be the result of per-host or per-recipient
	filtering.  This memo does not discuss the merits of any
	such filtering, but provides a mechanism to report such.
	This is useful only as a permanent error.
	};

    $rfc1893{'X.7.2'} = qq {  
	X.7.2   Mailing list expansion prohibited
	
	The sender is not authorized to send a message to the
	intended mailing list. This is useful only as a permanent
	error.
	};

    $rfc1893{'X.7.3'} = qq {  
	X.7.3   Security conversion required but not possible
	
	A conversion from one secure messaging protocol to another
	was required for delivery and such conversion was not
	possible. This is useful only as a permanent error.
	};

    $rfc1893{'X.7.4'} = qq {  
	A message contained security features such as secure
	authentication which could not be supported on the delivery
	protocol. This is useful only as a permanent error.
	};

    $rfc1893{'X.7.5'} = qq {  
	A transport system otherwise authorized to validate or
	decrypt a message in transport was unable to do so because
	necessary information such as key was not available or such
	information was invalid.
	};

    $rfc1893{'X.7.6'} = qq {  
	A transport system otherwise authorized to validate or
	decrypt a message was unable to do so because the necessary
	algorithm was not supported.
	};

    $rfc1893{'X.7.7'} = qq {  
	X.7.7   Message integrity failure
	
	A transport system otherwise authorized to validate a
	message was unable to do so because the message was
	corrupted or altered.  This may be useful as a permanent,
	transient persistent, or successful delivery code.
	};

    return "\n" . '-' x 72 . "\n" . $rfc1893{$key} . "\n";

}

END {

    my $self = shift;
    $parser->filer->purge
      if $parser;
}

1;
